<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\Phone;
use App\Utils\Session;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

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
        Auth::viaRequest(
            'crm',
            fn (Request $request) =>
            Session::get($request->bearerToken())
        );
    }
}
