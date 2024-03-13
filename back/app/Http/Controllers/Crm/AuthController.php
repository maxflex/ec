<?php

namespace App\Http\Controllers\Crm;

use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\{Client, Teacher, User, Phone};
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $phone = Phone::query()
            ->whereIn('entity_type', [
                User::class,
                Client::class,
                Teacher::class,
            ])
            ->whereNumber($request->phone)
            ->first();

        return new UserResource($phone);
    }

    public function user()
    {
        if (!auth('crm')->check()) {
            return response('', 401);
        }
        return new UserResource(auth('crm')->user());
    }
}
