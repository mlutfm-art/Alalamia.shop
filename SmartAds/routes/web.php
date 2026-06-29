<?php

use Illuminate\Support\Facades\Route;
use Modules\SmartAds\app\Http\Controllers\Admin\SmartAdController;

Route::group(["middleware" => ["web"]], function () {
    Route::group(["prefix" => config("route.admin_panel_link", "admin")], function () {
        Route::group(["prefix" => "smartads", "as" => "admin.smartads."], function () {

            Route::get("/list", [SmartAdController::class, "index"])->name('index');
            Route::get("/create", [SmartAdController::class, "create"])->name('create');
            Route::post("/store", [SmartAdController::class, "store"])->name('store');
            Route::get("/edit/{id}", [SmartAdController::class, "edit"])->name('edit');
            Route::post("/update/{id}", [SmartAdController::class, "update"])->name('update');
            Route::post("/delete", [SmartAdController::class, "destroy"])->name('delete');
            
            // 🚀 Enterprise Ajax Actions
            Route::post("/status-update", [SmartAdController::class, "toggle_status"])->name('status-update');
            Route::get("/duplicate/{id}", [SmartAdController::class, "duplicate"])->name('duplicate');
            Route::post("/send-firebase", [SmartAdController::class, "send_firebase"])->name('send-firebase');
            
            // 📊 الميزات والتقارير
            Route::get("/analytics/{id}", [SmartAdController::class, "analytics"])->name('analytics');
            Route::get("/dose-reminders", [SmartAdController::class, "doseReminders"])->name('dose-reminders');
            Route::get("/groups", [SmartAdController::class, "groups"])->name('groups');
            Route::get("/occasions", [SmartAdController::class, "occasions"])->name('occasions');
            Route::get("/segment-send", [SmartAdController::class, "segmentSend"])->name('segment-send');

            // 🤖 Search & Tools
            Route::get("/search-products", [SmartAdController::class, "searchProducts"])->name('search-products');
        });
    });
});
