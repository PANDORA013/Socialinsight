<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Services as Singletons
        $this->app->singleton(\App\Services\YouTubeService::class);
        $this->app->singleton(\App\Services\TwitterService::class);
        $this->app->singleton(\App\Services\TikTokService::class);
        $this->app->singleton(\App\Services\InstagramService::class);
        $this->app->singleton(\App\Services\SentimentService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('trend-analysis', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('trend-export', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });
    }
}
