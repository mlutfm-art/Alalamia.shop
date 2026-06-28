<?php
use Illuminate\Support\Facades\Route;
use Modules\SmartAds\app\Http\Controllers\FCMController;

Route::prefix('fcm')->group(function () {
    Route::post('/token/save',   [FCMController::class, 'saveToken']);
    Route::post('/token/delete', [FCMController::class, 'deleteToken']);
    Route::post('/send',         [FCMController::class, 'send']);
});
