<?php

use Illuminate\Support\Facades\Route;
use Modules\SmartAds\app\Http\Controllers\FCMController;
use Modules\SmartAds\app\Http\Controllers\RestAPI\SmartAdApiController;

/*
|--------------------------------------------------------------------------
| SmartAds API Routes
|--------------------------------------------------------------------------
*/

// يجب إضافة api/ هنا لأننا نقوم بتحميل الملف يدوياً في ServiceProvider
// المسار النهائي سيكون: your-domain.com/api/v1/smartads/...
Route::group(['prefix' => 'api/v1/smartads', 'middleware' => ['api']], function () {
    
    // 📢 جلب الإعلانات النشطة (يستدعيه التطبيق عند فتح الصفحة الرئيسية أو الأقسام)
    Route::get('/get-active/{placement}', [SmartAdApiController::class, 'get_active_ads']);
    
    // 🖼️ البنرات المنبثقة (In-App Banners)
    Route::get('/banners/pending',        [SmartAdApiController::class, 'get_pending_banners']);
    
    // 🔔 الإشعارات ومركز التنبيهات
    Route::get('/notifications',          [SmartAdApiController::class, 'get_notifications']);
    Route::post('/notifications/{id}/read', [SmartAdApiController::class, 'mark_notification_read']);

    // 📊 تتبع الإحصائيات (نقرات ومشاهدات)
    Route::post('/track-click/{id}',      [SmartAdApiController::class, 'track_click']);
    Route::post('/track-impression/{id}', [SmartAdApiController::class, 'track_impression']);

    // 📲 إدارة توكنات Firebase
    Route::prefix('fcm')->group(function () {
        Route::post('/token/save',   [FCMController::class, 'saveToken']);
        Route::post('/token/delete', [FCMController::class, 'deleteToken']);
    });
});
