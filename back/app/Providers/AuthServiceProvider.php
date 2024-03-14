<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\Phone;
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
        Auth::viaRequest('crm', function (Request $request) {
            $token = $request->bearerToken();
            logger("Bearer token: " . $token);
            if (!$token) {
                return null;
            }
            [$entityId, $entityType] = explode("|",  $token);
            return Phone::where([
                ['entity_id', $entityId],
                ['entity_type', $entityType]
            ])->first();
        });
    }
}
