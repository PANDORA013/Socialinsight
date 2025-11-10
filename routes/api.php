<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\YouTubeController;
use App\Http\Controllers\Api\TwitterController;
use App\Http\Controllers\Api\InstagramController;
use App\Http\Controllers\Api\TikTokController;
use App\Http\Controllers\Api\StatsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Social Media Analysis API Routes
Route::prefix('analyze')->group(function () {
    Route::post('/youtube', [YouTubeController::class, 'analyze']);
    Route::post('/twitter', [TwitterController::class, 'analyze']);
    Route::post('/instagram', [InstagramController::class, 'analyze']);
    Route::post('/tiktok', [TikTokController::class, 'analyze']);
});

// Stats & Export API
Route::get('/posts/stats', [StatsController::class, 'stats']);
Route::get('/posts', [StatsController::class, 'posts']);
Route::get('/posts/export', [StatsController::class, 'export']);

// TikTok Configuration Checker API
Route::get('/tiktok/check-config', function () {
    return response()->json([
        'uri' => env('TIKTOK_REDIRECT_URI'),
        'client_key' => env('TIKTOK_CLIENT_KEY') ? 'Configured' : 'Not set',
        'client_secret' => env('TIKTOK_CLIENT_SECRET') ? 'Configured' : 'Not set',
    ]);
});

// IndoBERT Test API
Route::prefix('test')->group(function () {
    Route::get('/indobert-status', function () {
        $service = app(\App\Services\IndoBERTService::class);
        return response()->json($service->getStatus());
    });
    
    Route::post('/indobert-analyze', function (Request $request) {
        $service = app(\App\Services\IndoBERTService::class);
        $text = $request->input('text');
        
        if (empty($text)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Text is required'
            ], 400);
        }
        
        $result = $service->analyzeSentiment($text);
        return response()->json($result);
    });
});
