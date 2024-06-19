<?php

namespace App\Http\Controllers\Common;

use App\Enums\LogType;
use App\Http\Controllers\Controller;
use App\Http\Resources\AuthResource;
use App\Http\Requests\AuthRequest;
use App\Models\Log;
use App\Utils\VerificationService;
use App\Models\Phone;
use App\Utils\Session;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(AuthRequest $request)
    {
        $phone = Phone::auth($request->phone);
        if ($phone->telegram_id) {
            VerificationService::sendCode($phone);
        }
        return new AuthResource($phone);
    }

    public function verifyCode(AuthRequest $request)
    {
        $phone = Phone::auth($request->phone);
        $this->logSuccess($phone);
        return [
            'user' => new AuthResource($phone),
            'token' => $phone->createSessionToken(),
        ];
    }

    public function user()
    {
        return new AuthResource(auth()->user());
    }

    public function logout(Request $request)
    {
        Session::logout($request->bearerToken());
    }

    private function logSuccess(Phone $phone)
    {
        Log::create([
            'entity_type' => $phone->entity_type,
            'entity_id' => $phone->entity_id,
            'type' => LogType::auth,
            'data' => [
                'status' => 'success',
                'ua' => $_SERVER['HTTP_USER_AGENT'],
            ],
        ]);
    }
}
