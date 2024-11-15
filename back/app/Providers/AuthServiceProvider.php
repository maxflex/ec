<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Utils\Session;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Custom user provider
        Auth::provider('phone-user-provider', function ($app, array $config) {
            return new PhoneUserProvider();
        });

        // Custom session handler
        Auth::viaRequest(
            'redis-session',
            fn (Request $request) =>
            Session::get($request->bearerToken())
        );
    }
}
