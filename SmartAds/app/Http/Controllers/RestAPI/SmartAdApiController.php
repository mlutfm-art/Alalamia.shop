<?php

namespace Modules\SmartAds\app\Http\Controllers\RestAPI;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\SmartAds\app\Models\SmartAd;
use Modules\SmartAds\app\Models\SmartAdNotification;
use Modules\SmartAds\app\Services\ActionResolverService;
use Modules\SmartAds\app\Services\FlutterResponseBuilder;

/**
 * ╔══════════════════════════════════════════════════════════════════╗
 * ║   SmartAdApiController — Flutter API v2                          ║
 * ║                                                                  ║
 * ║  Endpoints:                                                      ║
 * ║   GET  /api/v1/smartads/get-active/{placement}  الإعلانات النشطة ║
 * ║   POST /api/v1/smartads/track-click/{id}        تتبع الضغطات    ║
 * ║   POST /api/v1/smartads/track-impression/{id}   تتبع الظهور     ║
 * ║   GET  /api/v1/smartads/analytics/{id}          تحليلات A/B     ║
 * ║   GET  /api/v1/smartads/notifications           مركز الإشعارات  ║
 * ║   POST /api/v1/smartads/notifications/{id}/read قراءة إشعار     ║
 * ║   POST /api/v1/smartads/notifications/read-all  قراءة الكل      ║
 * ║   GET  /api/v1/smartads/action-types            أنواع الإجراءات ║
 * ╚══════════════════════════════════════════════════════════════════╝
 */
class SmartAdApiController extends Controller
{
    public function __construct(
        private readonly ActionResolverService  $resolver,
        private readonly FlutterResponseBuilder $builder,
    ) {}

    /*──────────────────────────────────────────────────────────────
     | 1. الإعلانات النشطة — الـ endpoint الرئيسي
     ──────────────────────────────────────────────────────────────*/

    /**
     * GET /api/v1/smartads/get-active/{placement}
     *
     * Query params:
     *   ?device=android|ios|web
     *   ?category_id=12
     *   ?region=SA
     *   ?user_id=45      (للـ sticky A/B)
     */
    public function get_active_ads(Request $request, string $placement): JsonResponse
    {
        try {
            $device     = $request->query('device');
            $categoryId = $request->query('category_id');
            $region     = $request->query('region');
            $userId     = $request->query('user_id');

            $ads = SmartAd::active()
                ->forPlacement($placement)
                ->forDevice($device)
                ->forCategory($categoryId)
                ->forRegion($region)
                ->whereNull('parent_id')
                ->get()
                ->map(function (SmartAd $ad) use ($userId) {
                    // A/B variant selection
                    $hasVariants = $ad->variants()->active()->exists();
                    if ($hasVariants) {
                        $chosen = $userId !== null
                            ? $this->stickyVariant($ad, (int) $userId)
                            : $ad->pickVariant();
                    } else {
                        $chosen = $ad;
                    }

                    $resolved = $this->resolver->resolve($chosen->action_data);
                    return $this->builder->build($chosen, $resolved, $ad->id);
                })
                ->values();

            return response()->json([
                'success' => true,
                'data'    => $ads,
            ]);

        } catch (\Throwable $e) {
            return $this->error('Failed to fetch ads', $e);
        }
    }

    /*──────────────────────────────────────────────────────────────
     | 2. التتبع (Click & Impression)
     ──────────────────────────────────────────────────────────────*/

    /**
     * POST /api/v1/smartads/track-click/{id}
     *
     * Body (اختياري):
     *   { "action_type": "instagram_follow", "user_id": 45 }
     */
    public function track_click(Request $request, int $id): JsonResponse
    {
        $ad = SmartAd::find($id);
        if (!$ad) return response()->json(['success' => false], 404);

        $ad->increment('clicks');

        return response()->json(['success' => true, 'total_clicks' => $ad->fresh()->clicks]);
    }

    /**
     * POST /api/v1/smartads/track-impression/{id}
     */
    public function track_impression(Request $request, int $id): JsonResponse
    {
        $ad = SmartAd::find($id);
        if (!$ad) return response()->json(['success' => false], 404);

        $ad->increment('impressions');

        return response()->json(['success' => true, 'total_impressions' => $ad->fresh()->impressions]);
    }

    /*──────────────────────────────────────────────────────────────
     | 3. التحليلات
     ──────────────────────────────────────────────────────────────*/

    /**
     * GET /api/v1/smartads/analytics/{id}
     */
    public function analytics(int $id): JsonResponse
    {
        $ad = SmartAd::with('variants')->find($id);
        if (!$ad) return response()->json(['success' => false], 404);

        $rows = collect([$ad])->merge($ad->variants)->map(fn (SmartAd $a) => [
            'id'          => $a->id,
            'title'       => $a->title,
            'ab_variant'  => $a->ab_variant,
            'impressions' => $a->impressions,
            'clicks'      => $a->clicks,
            'ctr'         => $a->ctr,
        ])->values();

        $winner = $ad->winner();

        return response()->json([
            'success' => true,
            'data'    => [
                'variants' => $rows,
                'winner'   => $winner ? ['id' => $winner->id, 'title' => $winner->title, 'ctr' => $winner->ctr] : null,
            ],
        ]);
    }

    /*──────────────────────────────────────────────────────────────
     | 4. مركز الإشعارات (Notification Center)
     ──────────────────────────────────────────────────────────────*/

    /**
     * GET /api/v1/smartads/notifications
     *
     * Query params:
     *   ?user_id=45
     *   ?display_type=notification_center  (اختياري: push|inapp_banner|notification_center)
     *   ?unread_only=1
     *   ?per_page=15
     */
    public function notifications(Request $request): JsonResponse
    {
        try {
            $userId      = $request->query('user_id');
            $displayType = $request->query('display_type');
            $unreadOnly  = $request->boolean('unread_only', false);
            $perPage     = min((int) $request->query('per_page', 15), 50);

            $query = SmartAdNotification::active()
                ->forUser($userId ? (int) $userId : null)
                ->latest();

            if ($displayType) {
                $query->forDisplay($displayType);
            }

            if ($unreadOnly) {
                $query->unread();
            }

            $paginated   = $query->paginate($perPage);
            $unreadCount = SmartAdNotification::active()
                ->forUser($userId ? (int) $userId : null)
                ->unread()
                ->count();

            return response()->json([
                'success'      => true,
                'unread_count' => $unreadCount,
                'data'         => $paginated->items(),
                'pagination'   => [
                    'current_page' => $paginated->currentPage(),
                    'last_page'    => $paginated->lastPage(),
                    'per_page'     => $paginated->perPage(),
                    'total'        => $paginated->total(),
                ],
            ]);

        } catch (\Throwable $e) {
            return $this->error('Failed to fetch notifications', $e);
        }
    }

    /**
     * POST /api/v1/smartads/notifications/{id}/read
     * تحديد إشعار واحد كمقروء.
     */
    public function markRead(int $id): JsonResponse
    {
        $notification = SmartAdNotification::find($id);
        if (!$notification) return response()->json(['success' => false], 404);

        $notification->markAsRead();
        return response()->json(['success' => true]);
    }

    /**
     * POST /api/v1/smartads/notifications/read-all
     * تحديد جميع إشعارات المستخدم كمقروءة.
     *
     * Body: { "user_id": 45 }
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $userId = $request->integer('user_id');

        $updated = SmartAdNotification::active()
            ->forUser($userId ?: null)
            ->unread()
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['success' => true, 'updated' => $updated]);
    }

    /*──────────────────────────────────────────────────────────────
     | 5. In-App Banner (إشعارات الظهور الفوري)
     ──────────────────────────────────────────────────────────────*/

    /**
     * GET /api/v1/smartads/banners/pending
     * البانرات المعلقة التي لم يراها المستخدم بعد.
     *
     * Query params: ?user_id=45&device=android
     */
    public function pendingBanners(Request $request): JsonResponse
    {
        try {
            $userId = $request->query('user_id');
            $device = $request->query('device');

            $banners = SmartAdNotification::active()
                ->unread()
                ->forUser($userId ? (int) $userId : null)
                ->forDisplay('inapp_banner')
                ->with('ad')
                ->latest()
                ->take(3)  // أقصى 3 بانرات في المرة الواحدة
                ->get()
                ->map(fn ($n) => [
                    'notification_id' => $n->id,
                    'ad_id'           => $n->smart_ad_id,
                    'title'           => $n->title,
                    'body'            => $n->body,
                    'image_url'       => $n->image_url,
                    'flutter_payload' => $n->flutter_payload,
                ]);

            return response()->json(['success' => true, 'data' => $banners]);

        } catch (\Throwable $e) {
            return $this->error('Failed to fetch banners', $e);
        }
    }

    /*──────────────────────────────────────────────────────────────
     | 6. أنواع الإجراءات (للـ Admin dynamic form عبر API)
     ──────────────────────────────────────────────────────────────*/

    /**
     * GET /api/v1/smartads/action-types
     */
    public function actionTypes(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => ActionResolverService::supportedTypes(),
        ]);
    }

    /*──────────────────────────────────────────────────────────────
     | Helpers الداخلية
     ──────────────────────────────────────────────────────────────*/

    /** Sticky A/B: نفس المستخدم يحصل دائماً على نفس النسخة */
    private function stickyVariant(SmartAd $ad, int $userId): SmartAd
    {
        $variants = $ad->variants()->active()->orderBy('id')->get();
        $pool     = $variants->push($ad)->values();
        $idx      = abs(crc32("{$userId}:{$ad->id}")) % $pool->count();
        return $pool[$idx];
    }

    /** استجابة خطأ موحدة */
    private function error(string $message, \Throwable $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error'   => config('app.debug') ? $e->getMessage() : null,
        ], 500);
    }
}
