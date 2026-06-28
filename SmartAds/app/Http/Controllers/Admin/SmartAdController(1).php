<?php

namespace Modules\SmartAds\app\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Utils\Helpers;
use App\Utils\ImageManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\SmartAds\app\Models\SmartAd;
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
        return view("smartads::admin.index", compact("ads", "search"));
    }

    public function create()
    {
        $products   = Product::active()->get();
        $categories = Category::where(["position" => 0])->get();
        $parents    = SmartAd::whereNull("parent_id")->latest()->get(["id", "title"]);
        return view("smartads::admin.create", compact("products", "categories", "parents"));
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
            // FCM: إشعار إعلان جديد
            try {
                (new FCMService())->sendToAll("📢 إعلان جديد!", $ad->title ?? "إعلان جديد في SmartAds");
            } catch (\Throwable $fcmErr) { /* لا نوقف العملية لو فشل FCM */ }
            return redirect()->route("admin.smartads.index");
        } catch (\Throwable $e) {
            Toastr::error("Something went wrong: " . $e->getMessage());
            return back()->withInput();
        }
    }

    public function edit($id)
    {
        $ad         = SmartAd::findOrFail($id);
        $products   = Product::active()->get();
        $categories = Category::where(["position" => 0])->get();
        $parents    = SmartAd::whereNull("parent_id")->where("id", "!=", $id)->latest()->get(["id", "title"]);
        return view("smartads::admin.edit", compact("ad", "products", "categories", "parents"));
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
            // FCM: إشعار تحديث الإعلان
            try {
                (new FCMService())->sendToAll("📝 تم تحديث إعلان", $ad->title ?? "تم تحديث إعلان في SmartAds");
            } catch (\Throwable $fcmErr) { /* تجاهل */ }
            return redirect()->route("admin.smartads.index");
        } catch (\Throwable $e) {
            Toastr::error("Something went wrong: " . $e->getMessage());
            return back()->withInput();
        }
    }

    public function delete(Request $request)
    {
        $ad = SmartAd::find($request->id);
        if (\ad) return response()->json(["success" => 0], 404);

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
        if (\ad) return response()->json(["success" => 0], 404);
        $ad->status = $request->status;
        $ad->save();
        // FCM: إشعار عند تفعيل الإعلان فقط
        if ((int)$request->status === 1) {
            try {
                (new FCMService())->sendToAll("✅ إعلان مفعّل", $ad->title ?? "تم تفعيل إعلان في SmartAds");
            } catch (\Throwable $fcmErr) { /* تجاهل */ }
        }
        return response()->json(["success" => 1, "message" => "Status updated successfully"], 200);
    }

    public function analytics($id)
    {
        $ad     = SmartAd::with("variants")->findOrFail($id);
        $winner = $ad->winner();
        return view("smartads::admin.analytics", compact("ad", "winner"));
    }


    public function sendPush(Request $request, $id)
    {
        $ad = SmartAd::findOrFail($id);
        try {
            $fcm = new \Modules\SmartAds\app\Services\FCMService();
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
            $fcm    = new \Modules\SmartAds\app\Services\FCMService();
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
        $notifications = collect([]);
        $unreadCount   = 0;
        return view("smartads::admin.notifications", compact("notifications", "unreadCount"));
    }

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
        ];

        $ad->parent_id          = $request->parent_id ?: null;
        $ad->ab_variant         = $request->ab_variant ?: null;
        $ad->target_category_id = $request->target_category_id ?: null;
        $ad->target_region      = $request->target_region ?: null;
        $ad->device_type        = $request->device_type ?: "all";
        $ad->start_at           = $request->start_at;
        $ad->end_at             = $request->end_at;
    }
}
