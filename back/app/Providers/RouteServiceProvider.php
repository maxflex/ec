<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('crm', function (Request $request) {
            return Limit::perMinute(1000)->by($request->user()?->id ?: $request->ip());
        });
        RateLimiter::for('pub', function (Request $request) {
            return Limit::perMinute(100)->by($request->ip());
        });
        $this->routes(function () {
            foreach (['common', 'admin', 'client', 'teacher'] as $r) {
                Route::middleware('crm')
                    ->prefix($r)
                    ->name("$r.")
                    ->group(base_path("routes/$r.php"));
            }
            Route::middleware('pub')
                ->prefix('pub')
                ->name('pub.')
                ->group(base_path('routes/pub.php'));
        });
    }
}
