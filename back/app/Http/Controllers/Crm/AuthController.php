<?php

namespace App\Http\Controllers\Crm;

use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\Phone;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $phone = Phone::auth($request->phone);
        return new UserResource($phone);
    }

    public function user()
    {
        if (!auth()->check()) {
            return response('', 401);
        }
        return new UserResource(auth()->user());
    }
}
