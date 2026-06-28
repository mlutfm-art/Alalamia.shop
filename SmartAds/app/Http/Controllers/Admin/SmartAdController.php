<?php

namespace Modules\SmartAds\app\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Utils\Helpers;
use App\Utils\ImageManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\SmartAds\app\Models\SmartAd;
use Modules\SmartAds\app\Models\SmartAdNotification;
use Modules\SmartAds\app\Services\FCMService;
use Modules\SmartAds\app\Services\TargetingService;
use Brian2694\Toastr\Facades\Toastr;

class SmartAdController extends Controller
{
    public function index(Request $request)
    {
        $query_param = [];
        $search = $request["search"];

        $ads = SmartAd::query()->whereNull("parent_id");

        if ($request->has("search") && $request["search"] !== null) {
            $key = explode(" ", $request["search"]);
            $ads->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere("title", "like", "%{$value}%");
                }
            });
            $query_param = ["search" => $request["search"]];
        }

        $ads = $ads->latest()->paginate(Helpers::pagination_limit())->appends($query_param);
        
        $stats = [
            'total'       => SmartAd::whereNull('parent_id')->count(),
            'active'      => SmartAd::where('status', 1)->whereNull('parent_id')->count(),
            'impressions' => SmartAd::sum('impressions') ?? 0,
            'clicks'      => SmartAd::sum('clicks') ?? 0,
        ];
        
        return view("smartads::admin.index", compact("ads", "search", "stats"));
    }

    public function create()
    {
        $products   = Product::active()->get();
        $categories = Category::where(["position" => 0])->get();
        $parents    = SmartAd::whereNull("parent_id")->latest()->get(["id", "title"]);
        $selectedUsers = collect();
        return view("smartads::admin.create", compact("products", "categories", "parents", "selectedUsers"));
    }

    public function store(Request $request)
    {
        $request->validate([
            "title"       => "required|string|max:191",
            "ad_type"     => "required|in:banner,popup,notification,video,native",
            "placement"   => "required|string",
            "image"       => "nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048",
            "video_file"  => "nullable|file|mimetypes:video/mp4,video/webm,video/quicktime|max:20480",
            "device_type" => "nullable|in:all,android,ios,web",
        ]);

        try {
            $ad = new SmartAd();
            $this->fillAd($ad, $request);
            $ad->status = 1;
            $ad->save();
            Toastr::success('تم إنشاء الإعلان بنجاح');
            return redirect()->route("admin.smartads.index");
        } catch (\Throwable $e) {
            Toastr::error("حدث خطأ: " . $e->getMessage());
            return back()->withInput();
        }
    }

    public function edit($id)
    {
        $ad         = SmartAd::findOrFail($id);
        $products   = Product::active()->get();
        $categories = Category::where(["position" => 0])->get();
        $parents    = SmartAd::whereNull("parent_id")->where("id", "!=", $id)->latest()->get(["id", "title"]);
        $actionData = is_array($ad->action_data) ? $ad->action_data : json_decode($ad->action_data, true);
        $selectedUsers = collect();
        if (!empty($actionData['target_value']['customer_id'])) {
            $selectedUsers = User::whereIn('id', (array)$actionData['target_value']['customer_id'])->get();
        }
        return view("smartads::admin.edit", compact("ad", "products", "categories", "parents", "selectedUsers"));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            "title"       => "required|string|max:191",
            "ad_type"     => "required|in:banner,popup,notification,video,native",
            "placement"   => "required|string",
            "image"       => "nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048",
            "video_file"  => "nullable|file|mimetypes:video/mp4,video/webm,video/quicktime|max:20480",
            "device_type" => "nullable|in:all,android,ios,web",
        ]);

        try {
            $ad = SmartAd::findOrFail($id);
            $this->fillAd($ad, $request, true);
            $ad->save();
            Toastr::success('تم تحديث الإعلان بنجاح');
            return redirect()->route("admin.smartads.index");
        } catch (\Throwable $e) {
            Toastr::error("حدث خطأ: " . $e->getMessage());
            return back()->withInput();
        }
    }

    public function delete(Request $request)
    {
        $ad = SmartAd::find($request->id);
        if (!$ad) return response()->json(["success" => 0], 404);

        if ($ad->image) ImageManager::delete("smartads/" . $ad->image);
        if ($ad->video) Storage::disk("public")->delete("smartads/videos/" . $ad->video);

        foreach ($ad->variants as $v) {
            if ($v->image) ImageManager::delete("smartads/" . $v->image);
            if ($v->video) Storage::disk("public")->delete("smartads/videos/" . $v->video);
            $v->delete();
        }
        $ad->delete();
        return response()->json(["success" => 1]);
    }

    public function status_update(Request $request)
    {
        $ad = SmartAd::find($request->id);
        if (!$ad) return response()->json(["success" => 0], 404);
        $ad->status = $request->status;
        $ad->save();
        return response()->json(["success" => 1, "message" => "تم تحديث الحالة بنجاح"], 200);
    }

    public function analytics($id)
    {
        $ad     = SmartAd::with("variants")->findOrFail($id);
        $winner = $ad->winner();
        return view("smartads::admin.analytics", compact("ad", "winner"));
    }

    // ============================================
    // 📱 إرسال الإعلان للتطبيق (FCM) مع الاستهداف الذكي
    // ============================================
    public function sendToApp($id)
    {
        $ad = SmartAd::findOrFail($id);
        
        $actionData = is_array($ad->action_data) 
            ? $ad->action_data 
            : json_decode($ad->action_data, true);
        
        $title = $ad->title ?? 'إعلان جديد';
        $body  = $actionData['description'] ?? $actionData['subtitle'] ?? 'تصفح الإعلان الآن';
        $image = $ad->image ? asset('storage/smartads/' . $ad->image) : null;
        
        // استخدام TargetingService إذا وُجد استهداف مخصص
        $targetType = $actionData['target_type'] ?? 'all';
        $targetValue = $actionData['target_value'] ?? [];

        if ($targetType !== 'all' && !empty($targetValue)) {
            $service = new TargetingService();
            $query = $service->buildTargetQuery($targetType, $targetValue, true);
            $tokens = $query->pluck('cm_firebase_token')->toArray();
        } else {
            // كل المستخدمين
            $tokens = User::whereNotNull('cm_firebase_token')
                ->where('cm_firebase_token', '!=', '')
                ->pluck('cm_firebase_token')
                ->toArray();
        }

        if (empty($tokens)) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد أجهزة مسجلة للإشعارات'
            ], 400);
        }
        
        try {
            $fcm = new FCMService();
            
            $data = [
                'ad_id'       => (string)$ad->id,
                'ad_type'     => $ad->ad_type,
                'action_type' => $actionData['type'] ?? 'none',
                'target'      => $actionData['target'] ?? '',
                'deep_link'   => $actionData['target'] ?? '',
                'image'       => $image ?? '',
                'button_text' => $actionData['button_text'] ?? 'عرض',
            ];
            
            $result = $fcm->sendToMultiple($tokens, $title, $body, $data);
            
            if (isset($result['success'])) {
                $ad->increment('impressions', $result['success']);
            }
            
            if (class_exists('\Modules\SmartAds\app\Models\SmartAdNotification')) {
                try {
                    SmartAdNotification::create([
                    'ad_id'        => $ad->id,
                    'title'        => $title,
                    'display_type' => $ad->ad_type,
                    'action_data'  => json_encode($actionData),
                    'status'       => 'sent',
                    'sent_count'   => $result['success'] ?? 0,
                    'failed_count' => $result['failure'] ?? 0,
                    'sent_at'      => now(),
                ]);
                } catch (\Exception $ex) {
                    Log::warning('SmartAdNotification creation failed: ' . $ex->getMessage());
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "تم الإرسال للتطبيق بنجاح",
                'result'  => [
                    'success' => $result['success'] ?? 0,
                    'failure' => $result['failure'] ?? 0,
                    'total'   => count($tokens),
                ]
            ]);
            
        } catch (\Throwable $e) {
            Log::error('SmartAds SendToApp Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'فشل الإرسال: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================
    // 🔍 دوال البحث والمعاينة للاستهداف
    // ============================================
    public function searchUsers(Request $request)
    {
        $search = $request->get("q");
        $users = User::select("id", "f_name", "l_name", "email", "phone")
            ->when($search, function($q) use ($search) {
                $q->where("f_name", "like", "%{$search}%")
                  ->orWhere("l_name", "like", "%{$search}%")
                  ->orWhere("email", "like", "%{$search}%")
                  ->orWhere("phone", "like", "%{$search}%");
            })
            ->limit(30)
            ->get();

        $results = array_values(array_map(function($u) {
            $text = trim($u["f_name"] . " " . $u["l_name"]);
            $sub = [];
            if (!empty($u["email"])) $sub[] = $u["email"];
            if (!empty($u["phone"])) $sub[] = $u["phone"];
            return [
                "id" => $u["id"],
                "text" => $text . (count($sub) ? " (" . implode(" | ", $sub) . ")" : ""),
                "email" => $u["email"] ?? "",
                "phone" => $u["phone"] ?? "",
                "name" => $text,
            ];
        }, $users->toArray()));

        return response()->json(["results" => $results]);
    }

    public function searchProducts(Request $request)
    {
        $search = $request->get('q');
        $products = Product::active()
            ->select('id', 'name')
            ->when($search, function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->limit(30)
            ->get();

        $results = [];
        foreach ($products as $p) {
            $results[] = ['id' => $p->id, 'text' => $p->name];
        }

        return response()->json(['results' => $results]);
    }

    public function previewTarget(Request $request)
    {
        $targetType = $request->input('target_type', 'all');
        $targetValue = [];

        switch ($targetType) {
            case 'customer':
                $targetValue['customer_id'] = $request->input('customer_id');
                break;
            case 'product':
                $targetValue['product_id'] = $request->input('product_id');
                break;
            case 'category':
                $targetValue['category_id'] = $request->input('category_id');
                break;
        }

        $service = new TargetingService();
        $total = $service->buildTargetQuery($targetType, $targetValue, false)->count();
        $fcmCount = $service->buildTargetQuery($targetType, $targetValue, true)->count();

        return response()->json([
            'total_users' => $total,
            'fcm_count'   => $fcmCount,
        ]);
    }

    public function sendPush(Request $request, $id)
    {
        $ad = SmartAd::findOrFail($id);
        try {
            $fcm = new FCMService();
            $body = $ad->action_data['description'] ?? $ad->action_data['subtitle'] ?? 'إعلان جديد من SmartAds';
            $result = $fcm->sendToAll($ad->title ?? 'إشعار جديد 📢', $body);
            return response()->json(['success' => true, 'result' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function sendPushNotification(Request $request)
    {
        try {
            $fcm    = new FCMService();
            $title  = $request->input("title", "إشعار جديد");
            $body   = $request->input("body", "");
            $token  = $request->input("token");
            $result = $token
                ? $fcm->sendToToken($token, $title, $body)
                : $fcm->sendToAll($title, $body);
            return response()->json(["success" => true, "result" => $result]);
        } catch (\Throwable $e) {
            return response()->json(["success" => false, "message" => $e->getMessage()], 500);
        }
    }

    public function sendToAll(Request $request)
    {
        return $this->sendPushNotification($request);
    }

    public function notificationsPage(Request $request)
    {
        $notifications = SmartAdNotification::latest()->paginate(20);
        $unreadCount   = SmartAdNotification::where('is_read', 0)->count();
        return view("smartads::admin.notifications", compact("notifications", "unreadCount"));
    }

    // ============================================
    // 🛠️ دالة تعبئة بيانات الإعلان (محدثة)
    // ============================================
    private function fillAd(SmartAd $ad, Request $request, bool $isUpdate = false): void
    {
        $ad->title     = $request->title;
        $ad->ad_type   = $request->ad_type;
        $ad->placement = $request->placement;

        if ($request->hasFile("image")) {
            $ad->image = $isUpdate && $ad->image
                ? ImageManager::update("smartads/", $ad->image, "webp", $request->file("image"))
                : ImageManager::upload("smartads/", "webp", $request->file("image"));
        }

        if ($request->hasFile("video_file")) {
            $file     = $request->file("video_file");
            $filename = Str::uuid() . "." . $file->getClientOriginalExtension();
            Storage::disk("public")->putFileAs("smartads/videos", $file, $filename);
            if ($isUpdate && $ad->video) {
                Storage::disk("public")->delete("smartads/videos/" . $ad->video);
            }
            $ad->video = $filename;
        } elseif ($request->filled("video_url")) {
            $ad->video = $request->video_url;
        }

        $ad->action_data = [
            "type"             => $request->action_type,
            "target"           => $request->action_target,
            "button_text"      => $request->button_text,
            "background_color" => $request->background_color,
            "text_color"       => $request->text_color,
            "subtitle"         => $request->subtitle,
            "description"      => $request->description,
            "extra"            => $request->extra ? json_decode($request->extra, true) : null,
            // 🆕 بيانات الاستهداف
            "target_type"      => $request->target_type ?? 'all',
            "target_value"     => $request->target_value ?? [],
        ];

        $ad->parent_id          = $request->parent_id ?: null;
        $ad->ab_variant         = $request->ab_variant ?: null;
        $ad->target_category_id = $request->target_category_id ?: null;
        $ad->target_region      = $request->target_region ?: null;
        $ad->device_type        = $request->device_type ?: "all";
        $ad->start_at           = $request->start_at;
        $ad->end_at             = $request->end_at;
    }


    public function schedules()
    {
        $schedules = \Modules\SmartAds\app\Models\SmartAdSchedule::latest()->paginate(20);
        $categories = \App\Models\Category::where("position", 0)->get();
        return view("smartads::admin.schedules", compact("schedules", "categories"));
    }

    public function storeSchedule(Request $request)
    {
        $validated = $request->validate([
            "title" => "required|string|max:191", "body" => "required|string|max:500",
            "target_type" => "required|in:all,customer,product,category",
            "scheduled_at" => "required|date|after:now", "image" => "nullable|image|max:2048",
        ]);
        $imagePath = $request->hasFile("image") ? $request->file("image")->store("smartads", "public") : null;
        $targetValue = [];
        if ($validated["target_type"] === "customer") $targetValue["customer_id"] = $request->customer_id;
        elseif ($validated["target_type"] === "product") $targetValue["product_id"] = $request->product_id;
        elseif ($validated["target_type"] === "category") $targetValue["category_id"] = $request->category_id;
        \Modules\SmartAds\app\Models\SmartAdSchedule::create([
            "title" => $validated["title"], "body" => $validated["body"], "image" => $imagePath,
            "target_type" => $validated["target_type"], "target_value" => $targetValue,
            "scheduled_at" => $validated["scheduled_at"], "status" => "pending",
        ]);
        return redirect()->route("admin.smartads.schedules")->with("success", "تمت جدولة الإعلان.");
    }

    public function deleteSchedule($id)
    {
        $schedule = \Modules\SmartAds\app\Models\SmartAdSchedule::findOrFail($id);
        if ($schedule->status === "pending") { $schedule->delete(); return back()->with("success", "تم الحذف."); }
        return back()->with("error", "لا يمكن حذف إعلان تم إرساله.");
    }

    public function doseReminders()
    {
        $reminders = \Modules\Alertmarkting\app\Models\ScheduledCampaign::with("product")->latest()->paginate(15);

        $stats = [
            "active"           => \Modules\Alertmarkting\app\Models\ScheduledCampaign::where("is_active", true)->count(),
            "inactive"         => \Modules\Alertmarkting\app\Models\ScheduledCampaign::where("is_active", false)->count(),
            "total_recipients" => \Modules\Alertmarkting\app\Models\ScheduledRecipient::count(),
            "active_recipients"=> \Modules\Alertmarkting\app\Models\ScheduledRecipient::where("status", "active")->count(),
            "completed"        => \Modules\Alertmarkting\app\Models\ScheduledRecipient::where("status", "completed")->count(),
            "total_sent"       => \Modules\Alertmarkting\app\Models\ScheduledNotifLog::where("status", "sent")->count(),
            "total_failed"     => \Modules\Alertmarkting\app\Models\ScheduledNotifLog::where("status", "failed")->count(),
            "today_sent"       => \Modules\Alertmarkting\app\Models\ScheduledNotifLog::where("status", "sent")->whereDate("created_at", today())->count(),
        ];

        $recentLogs = \Modules\Alertmarkting\app\Models\ScheduledNotifLog::with("campaign.product", "recipient.user")
            ->latest()->take(10)->get();

        return view("smartads::admin.dose-reminders", compact("reminders", "stats", "recentLogs"));
    }

    public function storeDoseReminder(Request $request)
    {
        $validated = $request->validate([
            "name" => "required|string|max:255", "product_id" => "required|exists:products,id",
            "notification_title" => "required|string|max:255", "notification_body" => "required|string|max:1000",
            "doses_count" => "required|integer|min:1", "days_between_doses" => "required|integer|min:1",
            "reminders_per_dose" => "required|integer|min:1",
        ]);
        \Modules\Alertmarkting\app\Models\ScheduledCampaign::create([
            "name" => $validated["name"], "campaign_type" => "dose_reminder",
            "notification_title" => $validated["notification_title"], "notification_body" => $validated["notification_body"],
            "product_id" => $validated["product_id"], "doses_count" => $validated["doses_count"],
            "days_between_doses" => $validated["days_between_doses"], "reminders_per_dose" => $validated["reminders_per_dose"],
            "is_active" => true,
        ]);
        return redirect()->route("admin.smartads.dose-reminders")->with("success", "تم إنشاء حملة تذكير الجرعات. يمكنك استخدام المتغيرات: {user_name}، {dose_number}، {total_doses}، {reminder_number}، {product_name}");
    }

    public function toggleDoseReminder($id)
    {
        $reminder = \Modules\Alertmarkting\app\Models\ScheduledCampaign::findOrFail($id);
        $reminder->is_active = !$reminder->is_active; $reminder->save();
        return back()->with("success", $reminder->is_active ? "تم تفعيل الحملة" : "تم إيقاف الحملة");
    }

    public function showDoseReminder($id)
    {
        $reminder = \Modules\Alertmarkting\app\Models\ScheduledCampaign::with("product")->findOrFail($id);
        $recipients = \Modules\Alertmarkting\app\Models\ScheduledRecipient::with("user")->where("campaign_id", $id)->latest()->paginate(10, ["*"], "recipients_page");
        $logs = \Modules\Alertmarkting\app\Models\ScheduledNotifLog::with("recipient.user")->where("campaign_id", $id)->latest()->paginate(15, ["*"], "logs_page");
        return view("smartads::admin.dose-reminder-show", compact("reminder", "recipients", "logs"));
    }





    public function testScheduled()
    {
        return view("smartads::admin.test-scheduled");
    }

    public function storeTestScheduled(Request $request)
    {
        $validated = $request->validate([
            "product_id" => "required|exists:products,id",
        ]);

        $scheduledAt = $request->scheduled_at 
            ? \Carbon\Carbon::parse($request->scheduled_at) 
            : now()->addMinute();

        \Modules\SmartAds\app\Models\SmartAdSchedule::create([
            "title" => "🧪 إشعار تجريبي",
            "body" => "هذا إشعار تجريبي تلقائي - تم إرساله بعد دقيقة واحدة من جدولته - لمشتري المنتج",
            "target_type" => "product",
            "target_value" => ["product_id" => $validated["product_id"]],
            "scheduled_at" => $scheduledAt,
            "status" => "pending",
        ]);

        return back()->with("success", "✅ تمت جدولة الإشعار التجريبي. سيتم إرساله تلقائياً بعد دقيقة واحدة لجميع مشتري المنتج.");
    }

    // ============================================
    // 🎉 المناسبات والأعياد
    // ============================================
    public function occasions()
    {
        $occasions = \Modules\Alertmarkting\app\Models\NotificationOccasion::orderBy('date')->paginate(20);
        $types = \Modules\Alertmarkting\app\Models\NotificationOccasion::types();
        $nextOccasion = \Modules\Alertmarkting\app\Models\NotificationOccasion::where('is_active', true)
            ->where('sent_this_year', false)->whereDate('date', '>=', now())->orderBy('date')->first();
        return view("smartads::admin.occasions", compact("occasions", "types", "nextOccasion"));
    }

    public function storeOccasion(Request $request)
    {
        $validated = $request->validate([
            "name" => "required|string|max:255",
            "type" => "required|in:religious,national,social,other",
            "date" => "required|date",
            "notification_title" => "required|string|max:255",
            "notification_body" => "required|string|max:1000",
            "send_before_days" => "nullable|integer|min:0",
            "send_at" => "nullable",
            "recurring" => "nullable|boolean",
        ]);
        \Modules\Alertmarkting\app\Models\NotificationOccasion::create($validated);
        Toastr::success("تمت إضافة المناسبة بنجاح");
        return redirect()->route("admin.smartads.occasions");
    }

    public function toggleOccasion($id)
    {
        $o = \Modules\Alertmarkting\app\Models\NotificationOccasion::findOrFail($id);
        $o->is_active = !$o->is_active; $o->save();
        return back()->with("success", $o->is_active ? "تم تفعيل المناسبة" : "تم إيقاف المناسبة");
    }

    public function deleteOccasion($id)
    {
        \Modules\Alertmarkting\app\Models\NotificationOccasion::findOrFail($id)->delete();
        Toastr::success("تم حذف المناسبة");
        return back();
    }

    public function resetOccasion($id)
    {
        $o = \Modules\Alertmarkting\app\Models\NotificationOccasion::findOrFail($id);
        $o->sent_this_year = false; $o->save();
        return back()->with("success", "تم إعادة تعيين المناسبة للإرسال");
    }

    public function segmentSend()
    {
        $segments = [
            "product_buyers"   => ["🛒","مشتري منتج محدد","استهدف من اشتروا منتجاً معيناً"],
            "price_range"      => ["💰","حسب قيمة الشراء","عملاء حسب المبلغ الذي دفعوه"],
            "order_status"     => ["📦","حالة الطلب","حسب حالة طلباتهم الحالية"],
            "last_order_days"  => ["🗓️","آخر طلب منذ","عملاء لم يطلبوا منذ فترة"],
            "city"             => ["📍","حسب المنطقة","عملاء في مدينة محددة"],
            "vip_customers"    => ["⭐","العملاء المميزون","الأكثر شراءً والأعلى قيمة"],
            "new_users"        => ["🆕","مستخدمون جدد","سجلوا حديثاً ولم يشتروا"],
            "category_buyers"  => ["🏷️","مهتم بقسم معين","اشتروا من قسم محدد"],
            "order_count"      => ["🔢","حسب عدد الطلبات","عملاء تجاوزوا عدد طلبات معين"],
            "payment_status"   => ["💳","حالة الدفع","من دفع أو لم يدفع بعد"],
            "abandoned_cart"   => ["🛒","سلة مهملة","أضافوا لسلتهم ولم يشتروا"],
            "registered_days"  => ["📅","أيام منذ التسجيل","حسب مدة تسجيلهم"],
        ];
        $categories = \App\Models\Category::where("position", 0)->get();
        return view("smartads::admin.segment-send", compact("segments", "categories"));
    }

    private function buildSegmentQuery($type, $request)
    {
        $query = \App\Models\User::query()->whereNotNull("cm_firebase_token")->where("cm_firebase_token", "!=", "")->where("cm_firebase_token", "not like", "eBFC%")->where("cm_firebase_token", "not like", "dPz%");
        
        switch ($type) {
            case "product_buyers":
                if ($request->product_id) $query->whereHas("orders.details", fn($q) => $q->where("product_id", $request->product_id));
                break;
            case "price_range":
                $query->whereHas("orders", fn($q) => $q->whereBetween("order_amount", [$request->min_price ?? 0, $request->max_price ?? 999999]));
                break;
            case "order_status":
                $query->whereHas("orders", fn($q) => $q->where("order_status", $request->order_status ?? "pending"));
                break;
            case "last_order_days":
                $days = $request->days ?? 30;
                $query->whereHas("orders", fn($q) => $q->where("created_at", "<=", now()->subDays($days)))->whereDoesntHave("orders", fn($q) => $q->where("created_at", ">", now()->subDays($days)));
                break;
            case "city":
                if ($request->city) $query->whereHas("orders.shippingAddress", fn($q) => $q->where("city", "like", "%" . $request->city . "%"));
                break;
            case "vip_customers":
                $query->has("orders", ">=", 3)->whereHas("orders", fn($q) => $q->where("order_amount", ">=", 10000));
                break;
            case "new_users":
                $query->where("created_at", ">=", now()->subDays(7))->whereDoesntHave("orders");
                break;
            case "category_buyers":
                if ($request->category_id) $query->whereHas("orders.details.product", fn($q) => $q->where("category_id", $request->category_id));
                break;
            case "order_count":
                $query->has("orders", ">=", $request->order_count ?? 5);
                break;
            case "payment_status":
                $query->whereHas("orders", fn($q) => $q->where("payment_status", $request->payment_status ?? "unpaid"));
                break;
            case "abandoned_cart":
                $query->whereHas("carts", fn($q) => $q->where("created_at", ">=", now()->subDays(3)))->whereDoesntHave("orders", fn($q) => $q->where("created_at", ">=", now()->subDays(3)));
                break;
            case "registered_days":
                $query->where("created_at", ">=", now()->subDays($request->days ?? 30));
                break;
        }
        return $query;
    }

    public function segmentPreview(Request $request)
    {
        $query = $this->buildSegmentQuery($request->type, $request);
        return response()->json(["total" => $query->count(), "fcm_count" => $query->where("cm_firebase_token", "not like", "eBFC%")->where("cm_firebase_token", "not like", "dPz%")->count()]);
    }

    public function segmentSendNow(Request $request)
    {
        $validated = $request->validate(["type" => "required", "title" => "required|string|max:255", "body" => "required|string|max:1000"]);
        $query = $this->buildSegmentQuery($request->type, $request);
        $tokens = $query->pluck("cm_firebase_token")->toArray();
        if (empty($tokens)) return response()->json(["success" => false, "message" => "لا يوجد مستلمين"]);
        $fcm = new \Modules\SmartAds\app\Services\FCMService();
        $result = $fcm->sendToMultiple($tokens, $validated["title"], $validated["body"]);
        return response()->json(["success" => true, "message" => "تم الإرسال لـ " . count($tokens) . " مستخدم"]);
    }

    public function templates()
    {
        $templates = \App\Models\NotificationTemplate::latest()->paginate(15);
        return view("admin.templates.index", compact("templates"));
    }

    public function storeTemplate(Request $request)
    {
        $data = $request->validate(["name" => "required", "title" => "required", "body" => "required"]);
        \App\Models\NotificationTemplate::create($data);
        return back()->with("success", "تم حفظ القالب");
    }

    public function deleteTemplate(Request $request)
    {
        \App\Models\NotificationTemplate::findOrFail($request->id)->delete();
        return back()->with("success", "تم حذف القالب");
    }
    public function chatbotDashboard()
    {
        return view("smartads::admin.chatbot-dashboard");
    }

}