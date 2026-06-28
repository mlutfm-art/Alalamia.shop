<?php

use Illuminate\Support\Facades\Route;
use Modules\SmartAds\app\Http\Controllers\Admin\SmartAdController;
use Modules\SmartAds\app\Http\Controllers\Admin\NotificationGroupController;
use Modules\SmartAds\app\Http\Controllers\FCMController;

Route::group(["middleware" => ["web"]], function () {
    Route::group(["prefix" => config("route.admin_panel_link", "admin")], function () {
        Route::group(["prefix" => "smartads"], function () {

            Route::get("/list", [SmartAdController::class, "index"])->name("admin.smartads.index");
            Route::get("/create", [SmartAdController::class, "create"])->name("admin.smartads.create");
            Route::post("/store", [SmartAdController::class, "store"])->name("admin.smartads.store");
            Route::get("/edit/{id}", [SmartAdController::class, "edit"])->name("admin.smartads.edit");
            Route::post("/update/{id}", [SmartAdController::class, "update"])->name("admin.smartads.update");
            Route::post("/delete", [SmartAdController::class, "destroy"])->name("admin.smartads.delete");
            Route::post("/status-update", [SmartAdController::class, "status_update"])->name("admin.smartads.status-update");
            Route::post("/{id}/push", [SmartAdController::class, "sendPush"])->name("admin.smartads.send-push");
            Route::get("/analytics/{id}", [SmartAdController::class, "analytics"])->name("admin.smartads.analytics");
            Route::get("/notifications", [SmartAdController::class, "notificationsPage"])->name("admin.smartads.notifications");
            Route::post("/{id}/send-to-app", [SmartAdController::class, "sendToApp"])->name("admin.smartads.send-to-app");
            Route::get("/schedules", [SmartAdController::class, "schedules"])->name("admin.smartads.schedules");
            Route::post("/schedules", [SmartAdController::class, "storeSchedule"])->name("admin.smartads.schedules.store");
            Route::post("/schedules/{id}/delete", [SmartAdController::class, "deleteSchedule"])->name("admin.smartads.schedules.delete");
            Route::get("/test-scheduled", [SmartAdController::class, "testScheduled"])->name("admin.smartads.test-scheduled");
            Route::post("/test-scheduled", [SmartAdController::class, "storeTestScheduled"])->name("admin.smartads.test-scheduled.store");
            Route::get("/dose-reminders", [SmartAdController::class, "doseReminders"])->name("admin.smartads.dose-reminders");
            Route::post("/dose-reminders", [SmartAdController::class, "storeDoseReminder"])->name("admin.smartads.dose-reminders.store");
            Route::post("/dose-reminders/{id}/toggle", [SmartAdController::class, "toggleDoseReminder"])->name("admin.smartads.dose-reminders.toggle");
            Route::get("/dose-reminders/{id}", [SmartAdController::class, "showDoseReminder"])->name("admin.smartads.dose-reminders.show");
            Route::get("/manual-send", [\App\Http\Controllers\SmartAdsManualSendController::class, "index"])->name("admin.smartads.manual-send");
            Route::post("/manual-send", [\App\Http\Controllers\SmartAdsManualSendController::class, "send"])->name("admin.smartads.manual-send.submit");
            Route::get("/search-users", [SmartAdController::class, "searchUsers"])->name("admin.smartads.search-users");
            Route::get("/search-products", [SmartAdController::class, "searchProducts"])->name("admin.smartads.search-products");
            Route::post("/preview-target", [SmartAdController::class, "previewTarget"])->name("admin.smartads.preview-target");

            // مجموعات الإشعارات
            Route::get("/groups", [NotificationGroupController::class, "index"])->name("admin.smartads.groups.index");
            Route::get("/groups/create", [NotificationGroupController::class, "create"])->name("admin.smartads.groups.create");
            Route::post("/groups", [NotificationGroupController::class, "store"])->name("admin.smartads.groups.store");
            Route::get("/groups/{id}/edit", [NotificationGroupController::class, "edit"])->name("admin.smartads.groups.edit");
            Route::put("/groups/{id}", [NotificationGroupController::class, "update"])->name("admin.smartads.groups.update");
            Route::delete("/groups/{id}", [NotificationGroupController::class, "destroy"])->name("admin.smartads.groups.destroy");
            Route::get("/groups/{id}/members", [NotificationGroupController::class, "members"])->name("admin.smartads.groups.members");
            Route::post("/groups/{id}/members/add", [NotificationGroupController::class, "addMember"])->name("admin.smartads.groups.members.add");
            Route::post("/groups/{id}/members/remove", [NotificationGroupController::class, "removeMember"])->name("admin.smartads.groups.members.remove");
            Route::get("/groups/{id}/send", [NotificationGroupController::class, "sendForm"])->name("admin.smartads.groups.send");
            Route::post("/groups/{id}/send", [NotificationGroupController::class, "sendNotification"])->name("admin.smartads.groups.send.notification");

            // المناسبات والأعياد
            Route::get("/occasions", [SmartAdController::class, "occasions"])->name("admin.smartads.occasions");
            Route::post("/occasions", [SmartAdController::class, "storeOccasion"])->name("admin.smartads.occasions.store");
            Route::post("/occasions/{id}/toggle", [SmartAdController::class, "toggleOccasion"])->name("admin.smartads.occasions.toggle");
            Route::post("/occasions/{id}/reset", [SmartAdController::class, "resetOccasion"])->name("admin.smartads.occasions.reset");
            Route::delete("/occasions/{id}", [SmartAdController::class, "deleteOccasion"])->name("admin.smartads.occasions.delete");
            // 📋 قوالب الرسائل
            Route::get("/notification-templates", [SmartAdController::class, "templates"])->name("admin.smartads.notification-templates");
            // 📋 قوالب الرسائل
            Route::get("/notification-templates", [SmartAdController::class, "templates"])->name("admin.smartads.notification-templates.index");
            Route::post("/notification-templates", [SmartAdController::class, "storeTemplate"])->name("admin.smartads.notification-templates.store");
            Route::delete("/notification-templates", [SmartAdController::class, "deleteTemplate"])->name("admin.smartads.notification-templates.destroy");
        });
    });
    Route::post("/send-push-direct", [SmartAdController::class, "sendPushNotification"])->name("admin.smartads.send-push-direct");
});

Route::get("/smartads/fcm/test", [FCMController::class, "testPage"])->name("smartads.fcm.test");

            // 📋 قوالب الرسائل

            // ✉️ إرسال مخصص
            Route::get("/custom-send", [SmartAdController::class, "customSend"])->name("admin.smartads.custom-send");
            Route::post("/custom-send", [SmartAdController::class, "customSendNow"])->name("admin.smartads.custom-send.send");

            // 🤖 لوحة تحكم البوت
            Route::get('/chatbot-dashboard', [SmartAdController::class, 'chatbotDashboard'])->name('admin.smartads.chatbot.dashboard');
