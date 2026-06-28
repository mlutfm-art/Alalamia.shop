<?php
use Illuminate\Support\Facades\Route;
use Modules\SmartAds\app\Http\Controllers\Admin\SmartAdController;

Route::middleware(['web', 'auth:admin'])->prefix('admin/smartads')->group(function () {
    Route::get('/segment-send', [SmartAdController::class, 'segmentSend'])->name('admin.smartads.segment-send');
    Route::post('/segment-preview', [SmartAdController::class, 'segmentPreview'])->name('admin.smartads.segment-preview');
    Route::post('/segment-send-now', [SmartAdController::class, 'segmentSendNow'])->name('admin.smartads.segment-send-now');
});
