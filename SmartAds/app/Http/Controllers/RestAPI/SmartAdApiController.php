<?php

namespace Modules\SmartAds\app\Http\Controllers\RestAPI;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\SmartAds\app\Models\SmartAd;
use Modules\SmartAds\app\Models\SmartAdNotification;
use Modules\SmartAds\app\Services\ActionResolverService;
use Modules\SmartAds\app\Services\FlutterResponseBuilder;

class SmartAdApiController extends Controller
{
    public function __construct(
        private readonly ActionResolverService  $resolver,
        private readonly FlutterResponseBuilder $builder,
    ) {}

    /**
     * جلب الإعلانات النشطة
     */
    public function get_active_ads(Request $request, string $placement): JsonResponse
    {
        try {
            $query = SmartAd::active();

            if ($placement !== 'all') {
                $query->where('placement', $placement);
            }

            if ($request->has('device')) {
                $query->where(function($q) use ($request) {
                    $q->whereNull('device_type')->orWhere('device_type', 'all')->orWhere('device_type', $request->device);
                });
            }

            $ads = $query->whereNull('parent_id')->latest()->get()->map(function ($ad) {
                $resolved = $this->resolver->resolve($ad->action_data);
                return $this->builder->build($ad, $resolved);
            });

            return response()->json(['success' => true, 'data' => $ads]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * جلب الإشعارات لمركز التنبيهات مع تحويل الـ Payload لصيغة يفهمها Flutter
     */
    public function get_notifications(Request $request): JsonResponse
    {
        try {
            $userId = $request->user_id;
            $notifications = SmartAdNotification::active()
                ->forUser($userId)
                ->latest()
                ->get()
                ->map(function($notification) {
                    // التأكد من أن الإشعار يحتوي على بيانات التوجيه (Action Engine)
                    $ad = $notification->ad;
                    $resolved = $this->resolver->resolve($notification->flutter_payload ?? ($ad ? $ad->action_data : []));
                    
                    return [
                        'id' => $notification->id,
                        'title' => $notification->title,
                        'body' => $notification->body,
                        'image' => $notification->image_url,
                        'is_read' => (bool)$notification->is_read,
                        'created_at' => $notification->created_at->toIso8601String(),
                        'action_engine' => [
                            'type' => $resolved['action_type'],
                            'payload' => $resolved['payload'],
                            'deep_link' => $resolved['deep_link'],
                        ]
                    ];
                });

            return response()->json(['success' => true, 'data' => $notifications]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function mark_notification_read(Request $request, int $id): JsonResponse
    {
        $notification = SmartAdNotification::find($id);
        if ($notification) {
            $notification->markAsRead();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }

    public function track_click(Request $request, int $id): JsonResponse
    {
        $ad = SmartAd::find($id);
        if ($ad) {
            $ad->increment('clicks');
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }

    public function track_impression(Request $request, int $id): JsonResponse
    {
        SmartAd::where('id', $id)->increment('impressions');
        return response()->json(['success' => true]);
    }
}
