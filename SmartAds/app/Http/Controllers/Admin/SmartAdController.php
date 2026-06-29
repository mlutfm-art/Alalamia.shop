<?php

namespace Modules\SmartAds\app\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Utils\Helpers;
use App\Utils\ImageManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\SmartAds\app\Models\SmartAd;
use Modules\SmartAds\app\Services\ActionResolverService;
use Modules\SmartAds\app\Services\EngagementFlowService;
use Modules\SmartAds\app\Services\TargetingService;
use Brian2694\Toastr\Facades\Toastr;

class SmartAdController extends Controller
{
    public function __construct(
        private readonly ActionResolverService $resolver,
        private readonly EngagementFlowService $engagementFlow,
        private readonly TargetingService $targetingService
    ) {}

    public function index(Request $request)
    {
        $ads = SmartAd::latest()->paginate(Helpers::pagination_limit());
        
        // 📊 إحصائيات كاملة لضمان عدم ظهور خطأ "total_sent" والمفاتيح الأخرى
        $stats = [
            'total'           => SmartAd::count(),
            'active'          => SmartAd::where('status', 1)->count(),
            'total_sent'      => SmartAd::sum('sent_count') ?? 0,
            'total_delivered' => SmartAd::sum('delivered_count') ?? 0,
            'total_opened'    => SmartAd::sum('opened_count') ?? 0,
            'impressions'     => SmartAd::sum('impressions') ?? 0,
            'clicks'          => SmartAd::sum('clicks') ?? 0,
            'conversions'     => SmartAd::sum('conversion_count') ?? 0,
        ];

        return view("smartads::admin.index", compact("ads", "stats"));
    }

    public function create()
    {
        $categories = Category::where('position', 0)->get();
        return view("smartads::admin.create", compact("categories"));
    }

    public function edit($id)
    {
        $ad = SmartAd::findOrFail($id);
        $categories = Category::where('position', 0)->get();
        return view("smartads::admin.edit", compact("ad", "categories"));
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $ad = new SmartAd();
            $this->fillAd($ad, $request);
            $ad->save();
            DB::commit();
            Toastr::success('تم إنشاء الإعلان بنجاح');
            return redirect()->route('admin.smartads.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("SmartAds Store Error: " . $e->getMessage());
            Toastr::error('خطأ: ' . $e->getMessage());
            return back()->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $ad = SmartAd::findOrFail($id);
            $this->fillAd($ad, $request, true);
            $ad->save();
            DB::commit();
            Toastr::success('تم التحديث بنجاح');
            return redirect()->route('admin.smartads.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error('فشل التحديث');
            return back();
        }
    }

    public function destroy(Request $request)
    {
        $ad = SmartAd::findOrFail($request->id);
        if ($ad->image) {
            ImageManager::delete('smartads/' . $ad->image);
        }
        $ad->delete();
        return response()->json(['success' => true]);
    }

    public function toggle_status(Request $request)
    {
        $ad = SmartAd::findOrFail($request->id);
        $ad->status = $request->status;
        $ad->save();
        return response()->json(['success' => true]);
    }

    public function send_firebase(Request $request)
    {
        $ad = SmartAd::findOrFail($request->id);
        $result = $this->engagementFlow->trigger($ad, ['send_push' => true]);
        return response()->json(['success' => true, 'message' => 'تم الإرسال بنجاح', 'result' => $result]);
    }

    // 🚀 دوال الصفحات الإضافية المطلوبة لتجنب خطأ 404
    public function doseReminders() { return view("smartads::admin.dose-reminders"); }
    public function groups() { return view("smartads::admin.groups"); }
    public function occasions() { return view("smartads::admin.occasions"); }
    public function segmentSend() { return view("smartads::admin.segment-send"); }

    private function fillAd(SmartAd $ad, Request $request, $isUpdate = false)
    {
        $ad->title = $request->title;
        $ad->sub_title = $request->sub_title;
        $ad->ad_type = $request->ad_type;
        $ad->action_type = $request->action_type;
        $ad->placement = $request->placement ?? 'home';
        $ad->button_text = $request->button_text ?? 'عرض الآن';

        if ($request->hasFile('image')) {
            $ad->image = $isUpdate 
                ? ImageManager::update('smartads/', $ad->image, 'webp', $request->file('image')) 
                : ImageManager::upload('smartads/', 'webp', $request->file('image'));
        }

        $ad->action_data = [
            'type' => $request->action_type,
            'id' => $request->product_id ?? $request->category_id ?? $request->social_id ?? null,
            'url' => $request->external_url,
            'phone' => $request->wa_phone,
            'coupon' => $request->coupon_code,
            'background_color' => $request->background_color ?? '#377dff',
        ];
        
        $ad->firebase_payload = $request->firebase_payload;
        $ad->targeting_config = $request->targeting_config;
        $ad->start_at = $request->start_at;
        $ad->end_at = $request->end_at;
        
        if(!$isUpdate) $ad->status = 1;
    }

    public function searchProducts(Request $request)
    {
        $products = Product::active()->where('name', 'like', "%{$request->q}%")->limit(10)->get();
        return response()->json(['results' => $products->map(fn($p) => ['id'=>$p->id, 'text'=>$p->name])]);
    }
}
