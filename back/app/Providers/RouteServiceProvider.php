<?php

namespace App\Providers;

use App\Enums\RouteGroup;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->routes(function () {
            foreach (RouteGroup::cases() as $routeGroup) {
                $name = enum_value($routeGroup);
                $middleware = [
                    'throttle:'.$name,
                    SubstituteBindings::class,
                ];

                // всё, кроме pub, под логином
                if ($routeGroup !== RouteGroup::pub) {
                    $middleware[] = 'auth:crm';
                }

                Route::prefix($name)
                    ->name($name.'.')
                    ->middleware($middleware)
                    ->group(base_path("routes/$name.php"));
            }
        });

        $this->setRateLimiters();
    }

    private function setRateLimiters()
    {
        // Условно безлимитно
        RateLimiter::for(RouteGroup::admin,
            fn ($request) => Limit::perMinute(500)->by($request->user()->id)
        );

        RateLimiter::for(RouteGroup::client,
            fn ($request) => Limit::perMinute(50)->by($request->user()->id)
        );

        RateLimiter::for(RouteGroup::teacher,
            fn ($request) => Limit::perMinute(50)->by($request->user()->id)
        );

        RateLimiter::for(RouteGroup::pub,
            fn ($request) => Limit::perMinute(100)->by($request->ip())
        );

        /**
         * Заявки с сайта
         * 5 заявок в час с одного IP или google_id, или yandex_id, или phone
         */
        RateLimiter::for('requests', function (Request $request) {
            // тестовые заявки с health-check
            if ($request->input('testing') === true) {
                return Limit::none();
            }

            $maxAttempts = 5;

            $limits = [
                Limit::perHour($maxAttempts)->by($request->ip()),
            ];

            $fields = ['yandex_id', 'google_id', 'phone'];
            foreach ($fields as $field) {
                if ($request->input($field)) {
                    $limits[] = Limit::perHour($maxAttempts)->by($request->input($field));
                }
            }

            return $limits;
        });

    }
}
