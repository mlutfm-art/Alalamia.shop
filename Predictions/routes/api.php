<?php
use Illuminate\Support\Facades\Route;
use Modules\Predictions\app\Http\Controllers\API\PredictionApiController;

/*
 * All routes are public at the routing layer.
 * Auth is checked manually inside the controller so we
 * can return proper JSON errors instead of middleware redirects.
 */
Route::prefix('predictions')->group(function() {
    Route::get('/matches/active',     [PredictionApiController::class,'activeMatches']);
    Route::get('/matches/{id}',       [PredictionApiController::class,'singleMatch']);
    Route::get('/leaderboard',        [PredictionApiController::class,'leaderboard']);
    Route::post('/submit',            [PredictionApiController::class,'submit']);
    Route::get('/my-predictions',     [PredictionApiController::class,'myPredictions']);
    Route::get('/banner',             [PredictionApiController::class,'activeBanner']);
});
