<?php
use Illuminate\Support\Facades\Route;
use Modules\Predictions\app\Http\Controllers\Admin\PredictionController;

Route::group(['prefix'=>'admin/predictions','as'=>'admin.predictions.','middleware'=>['admin','actch:admin_panel']], function() {
    Route::get('/',                         [PredictionController::class,'index'])->name('index');
    Route::get('/matches',                  [PredictionController::class,'matchesList'])->name('matches');
    Route::post('/matches',                 [PredictionController::class,'store'])->name('matches.store');
    Route::put('/matches/{id}',             [PredictionController::class,'update'])->name('matches.update');
    Route::delete('/matches/{id}',          [PredictionController::class,'destroy'])->name('matches.destroy');
    Route::post('/matches/{id}/result',     [PredictionController::class,'submitResult'])->name('matches.result');
    Route::post('/matches/{id}/notify',     [PredictionController::class,'notify'])->name('matches.notify');
    Route::get('/list',                     [PredictionController::class,'predictionsList'])->name('list');
    Route::get('/leaderboard',              [PredictionController::class,'leaderboard'])->name('leaderboard');
    Route::get('/settings',                 [PredictionController::class,'settings'])->name('settings');
    Route::post('/settings',                [PredictionController::class,'updateSettings'])->name('settings.update');
});
