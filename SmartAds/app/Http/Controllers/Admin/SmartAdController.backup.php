<?php

namespace Modules\SmartAds\app\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Utils\Helpers;
use App\Utils\ImageManager;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\SmartAds\app\Models\SmartAd;
use Modules\SmartAds\app\Models\SmartAdNotification;
use Modules\SmartAds\app\Services\ActionResolverService;
use Modules\SmartAds\app\Services\EngagementFlowService;

class SmartAdController extends Controller
{
    public function __construct(
        private readonly EngagementFlowService $flowService,
        private readonly ActionResolverService  $resolver,
    ) {}

    /*──────────────────────────────────────────────────────────────
     | CRUD
     ──────────────────────────────────────────────────────────────*/

    public function index(Request $request)
    {
        $search = $request['search'];
        $ads = SmartAd::query()->whereNull('parent_id');

        if ($request->filled('search')) {
            $key = explode(' ', $request['search']);
            $ads->where(function ($q) use ($key) {
                foreach ($key as $v) { $q->orWhere('title', 'like', "%{$v}%"); }
            });
        }
        if ($request->filled('ad_type')) {
            $ads->where('ad_type', $request['ad_type']);
        }
        if ($request->filled('action_type')) {
            $ads->whereJsonContains('action_data->type', $request['action_type']);
        }

        $ads = $ads->latest()->paginate(Helpers::pagination_limit())->appends($request->query());
        return view('smartads::admin.index', compact('ads', 'search'));
    }

    public function create()
    {
        $products    = Product::active()->get();
        $categories  = Category::where(['position' => 0])->get();
        $parents     = SmartAd::whereNull('parent_id')->latest()->get(['id', 'title']);
        return view('smartads::admin.create', compact('products', 'categories', 'parents'));
    }

    public function store(Request $request)
    {
        $request->validate($this->rules());
        try {
            $ad = new SmartAd();
            $this->fillAd($ad, $request);
            $ad->status = 1;
            $ad->save();

            $this->flowService->trigger($ad, [
                'send_push' => $request->boolean('send_push_now', true) && is_null($ad->parent_id),
                'topic'     => $request->input('push_topic', config('smartads.default_push_topic')),
            ]);

            Toastr::success('تم إضافة الإعلان بنجاح!');
            return redirect()->route('admin.smartads.index');
        } catch (\Throwable $e) {
            Toastr::error('حدث خطأ: ' . $e->getMessage());
            return back()->withInput();
        }
    }

    public function edit($id)
    {
        $ad         = SmartAd::findOrFail($id);
        $products   = Product::active()->get();
        $categories = Category::where(['position' => 0])->get();
        $parents    = SmartAd::whereNull('parent_id')->where('id', '!=', $id)->latest()->get(['id', 'title']);
        return view('smartads::admin.edit', compact('ad', 'products', 'categories', 'parents'));
    }

    public function update(Request $request, $id)
    {
        $request->validate($this->rules());
        try {
            $ad = SmartAd::findOrFail($id);
            $this->fillAd($ad, $request, true);
            $ad->save();

            if ($request->boolean('retrigger_push', false)) {
                $this->flowService->trigger($ad, [
                    'send_push' => true,
                    'topic'     => $request->input('push_topic', config('smartads.default_push_topic')),
                ]);
            }

            Toastr::success('تم تحديث الإعلان بنجاح!');
            return redirect()->route('admin.smartads.index');
        } catch (\Throwable $e) {
            Toastr::error('حدث خطأ: ' . $e->getMessage());
            return back()->withInput();
        }
    }

    public function delete(Request $request)
    {
        $ad = SmartAd::find($request->id);
        if (!$ad) return response()->json(['success' => 0], 404);

        if ($ad->image) ImageManager::delete('smartads/' . $ad->image);
        if ($ad->video) Storage::disk('public')->delete('smartads/videos/' . $ad->video);

        foreach ($ad->variants as $v) {
            if ($v->image) ImageManager::delete('smartads/' . $v->image);
            if ($v->video) Storage::disk('public')->delete('smartads/videos/' . $v->video);
            $v->delete();
        }
        $ad->delete();
        return response()->json(['success' => 1]);
    }

    public function status_update(Request $request)
    {
        $ad = SmartAd::find($request->id);
        if (!$ad) return response()->json(['success' => 0], 404);
        $ad->update(['status' => $request->status]);
        return response()->json(['success' => 1]);
    }

    public function analytics($id)
    {
        $ad     = SmartAd::with('variants')->findOrFail($id);
        $winner = $ad->winner();
        return view('smartads::admin.analytics', compact('ad', 'winner'));
    }

    /*──────────────────────────────────────────────────────────────
     | مركز الإشعارات (Admin View)
     ──────────────────────────────────────────────────────────────*/

    public function notificationsPage(Request $request)
    {
        $query = SmartAdNotification::active()->latest();

        if ($request->filled('display_type')) {
            $query->forDisplay($request->display_type);
        }
        if ($request->boolean('unread_only')) {
            $query->unread();
        }

        $notifications = $query->paginate(20)->appends($request->query());
        $unreadCount   = SmartAdNotification::active()->unread()->count();

        return view('smartads::admin.notifications', compact('notifications', 'unreadCount'));
    }

    /*──────────────────────────────────────────────────────────────
     | إرسال Push يدوي
     ──────────────────────────────────────────────────────────────*/

    public function sendPush(Request $request, $id)
    {
        $ad = SmartAd::findOrFail($id);
        $result = $this->flowService->trigger($ad, [
            'send_push' => true,
            'topic'     => $request->input('topic', config('smartads.default_push_topic')),
            'user_ids'  => $request->input('user_ids', []),
        ]);

private function fillAd(SmartAd $ad, Request $request, bool $isUpdate = false): void
{
    $ad->title     = $request->title;
    $ad->ad_type   = $request->ad_type;
    $ad->placement = $request->placement;

    // Image
    if ($request->hasFile('image')) {
        $ad->image = $isUpdate && $ad->image
            ? ImageManager::update('smartads/', $ad->image, 'webp', $request->file('image'))
            : ImageManager::upload('smartads/', 'webp', $request->file('image'));
    }

    // Video
    if ($request->hasFile('video_file')) {
        $file = $request->file('video_file');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        Storage::disk('public')->putFileAs('smartads/videos', $file, $filename);

        if ($isUpdate && $ad->video && !str_starts_with($ad->video, 'http')) {
            Storage::disk('public')->delete('smartads/videos/' . $ad->video);
        }

        $ad->video = $filename;
    } elseif ($request->filled('video_url')) {
        $ad->video = $request->video_url;
    }

    // Action Data (مهم: بدون أي return هنا)
    $actionType = $request->action_type;
    $payload    = $this->buildPayload($actionType, $request);

    $ad->action_data = [
        'type'             => $actionType,
        'payload'          => $payload,
        'button_text'      => $request->button_text ?? 'اعرف أكثر',
        'background_color' => $request->background_color ?? '#ffffff',
        'text_color'       => $request->text_color ?? '#212121',
        'subtitle'         => $request->subtitle ?? '',
        'description'      => $request->description ?? '',
        'target'           => $request->action_target ?? null,
        'extra'            => $request->extra ? json_decode($request->extra, true) : null,
    ];

    // A/B
    $ad->parent_id  = $request->parent_id ?: null;
    $ad->ab_variant = $request->ab_variant ?: null;

    // Targeting
    $ad->target_category_id = $request->target_category_id ?: null;
    $ad->target_region      = $request->target_region ?: null;
}

