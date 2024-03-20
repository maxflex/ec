<?php

namespace App\Http\Controllers\Crm;

use App\Http\Resources\UserResource;
use App\Models\Phone;
use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequest;
use App\Utils\VerificationService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(AuthRequest $request)
    {
        $phone = Phone::auth($request->phone);
        if ($phone->telegram_id) {
            VerificationService::sendCode($phone);
        }
        return new UserResource($phone);
    }

    public function verifyCode(AuthRequest $request)
    {
        $request->validate([
            'code' => ['required', 'string']
        ]);
        $phone = Phone::auth($request->phone);
        return [
            'verified' => VerificationService::verifyCode(
                $phone,
                $request->code
            )
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
