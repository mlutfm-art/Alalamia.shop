<?php

use Illuminate\Support\Facades\Route;
use Modules\SmartAds\app\Http\Controllers\Admin\SmartAdController;
use Modules\SmartAds\app\Http\Controllers\Admin\NotificationGroupController;

Route::group(["middleware" => ["web"]], function () {
    Route::group(["prefix" => config("route.admin_panel_link", "admin")], function () {
        Route::group(["prefix" => "smartads", "as" => "admin.smartads."], function () {

            Route::get("/list", [SmartAdController::class, "index"])->name('index');
            Route::get("/create", [SmartAdController::class, "create"])->name('create');
            Route::post("/store", [SmartAdController::class, "store"])->name('store');
            Route::get("/edit/{id}", [SmartAdController::class, "edit"])->name('edit');
            Route::post("/update/{id}", [SmartAdController::class, "update"])->name('update');
            Route::post("/delete", [SmartAdController::class, "destroy"])->name('delete');
            
            // 🚀 الإجراءات السريعة
            Route::post("/status-update", [SmartAdController::class, "toggle_status"])->name('status-update');
            Route::post("/send-firebase", [SmartAdController::class, "send_firebase"])->name('send-firebase');
            
            // 🗓️ المناسبات والجرعات والفئات
            Route::get("/dose-reminders", [SmartAdController::class, "doseReminders"])->name('dose-reminders');
            Route::get("/occasions", [SmartAdController::class, "occasions"])->name('occasions');
            Route::get("/segment-send", [SmartAdController::class, "segmentSend"])->name('segment-send');

            // 👥 إدارة المجموعات (تحويل للمتحكم المتخصص)
            Route::group(["prefix" => "groups", "as" => "groups."], function() {
                Route::get("/", [NotificationGroupController::class, "index"])->name('index');
                Route::get("/create", [NotificationGroupController::class, "create"])->name('create');
                Route::post("/store", [NotificationGroupController::class, "store"])->name('store');
                Route::get("/edit/{id}", [NotificationGroupController::class, "edit"])->name('edit');
                Route::post("/update/{id}", [NotificationGroupController::class, "update"])->name('update');
                Route::post("/delete/{id}", [NotificationGroupController::class, "destroy"])->name('delete');
                Route::get("/members/{id}", [NotificationGroupController::class, "members"])->name('members');
                Route::post("/add-member/{id}", [NotificationGroupController::class, "addMember"])->name('add-member');
                Route::post("/remove-member/{id}", [NotificationGroupController::class, "removeMember"])->name('remove-member');
                Route::get("/send/{id}", [NotificationGroupController::class, "sendForm"])->name('send-form');
                Route::post("/send/{id}", [NotificationGroupController::class, "sendNotification"])->name('send-notification');
            });

            // 🤖 أدوات البحث
            Route::get("/search-products", [SmartAdController::class, "searchProducts"])->name('search-products');
        });
    });
});
