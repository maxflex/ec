<?php

namespace App\Http\Controllers\Crm;

use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\Phone;
use App\Http\Controllers\Controller;
use App\Utils\VerificationService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $phone = Phone::auth($request->phone);
        if ($phone->telegram_id) {
            VerificationService::sendCode($phone);
        }
        return new UserResource($phone);
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string']
        ]);

        return [
            'verified' => VerificationService::verifyCode($request->code)
        ];
    }

    public function user()
    {
        if (!auth()->check()) {
            return response('', 401);
        }
        return new UserResource(auth()->user());
    }
}
