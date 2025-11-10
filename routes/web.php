<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrendController;
use App\Http\Controllers\TikTokAuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Home - Trend Search Interface
Route::get('/', [TrendController::class, 'home'])->name('home');

// TikTok Login Demo Page (for review)
Route::view('/tiktok-login-demo', 'tiktok-login-demo')->name('tiktok.demo');

// Trend Analysis
Route::post('/analyze/trend', [TrendController::class, 'analyzeTrend'])->name('analyze.trend');
Route::get('/export/trend', [TrendController::class, 'exportTrend'])->name('export.trend');

// TikTok OAuth Routes
Route::get('/auth/tiktok', [TikTokAuthController::class, 'redirectToTikTok'])->name('tiktok.login');
Route::get('/auth/tiktok/callback', [TikTokAuthController::class, 'handleTikTokCallback'])->name('tiktok.callback');
Route::get('/auth/tiktok/disconnect', [TikTokAuthController::class, 'disconnect'])->name('tiktok.disconnect');
Route::get('/auth/tiktok/status', [TikTokAuthController::class, 'checkConnection'])->name('tiktok.status');

// TikTok Dashboard - Show user's videos using access token
Route::get('/tiktok/dashboard', [TikTokAuthController::class, 'showDashboard'])->name('tiktok.dashboard');

// TikTok URI Checker Tool (for debugging)
Route::view('/tiktok/uri-checker', 'tiktok-uri-checker')->name('tiktok.uri-checker');
