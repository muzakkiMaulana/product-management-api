<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for(
            'product-writes',
            fn (Request $request) => Limit::perSecond(1, 5)->by($request->user()->id),
        );

        RateLimiter::for('auth', fn (Request $request) => Limit::perMinute(3)->by($request->ip()));

        RateLimiter::for('token-refresh', fn (Request $request) => Limit::perMinute(3)->by($request->ip()));
    }
}
