<?php

namespace App\Http\Controllers\Common;

use App\Enums\LogType;
use App\Http\Controllers\Controller;
use App\Http\Requests\{SubmitPhoneRequest, VerifyCodeRequest};
use App\Http\Resources\{AuthResource, PhoneResource};
use App\Models\{Log, Phone};
use App\Utils\{Session, VerificationService};
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Первый шаг авторизации: ввели номер телефона
     */
    public function submitPhone(SubmitPhoneRequest $request)
    {
        $phone = Phone::auth($request->input('number'));
        if ($phone->telegram_id) {
            VerificationService::sendCode($phone);
        }
        return [
            'user' => new AuthResource($phone->entity),
            'phone' => new PhoneResource($phone),
        ];
    }

    /**
     * Второй шаг авторизации: подтверждение кода подтверждения
     * При успешном подтверждении происходит вход в систему (создаётся сессия)
     */
    public function verifyCode(VerifyCodeRequest $request)
    {
        $phone = Phone::find($request->phone_id);
        $token = Session::logIn($phone);
        $this->logSuccess($phone);
        return [
            'user' => new AuthResource($phone->entity),
            'token' => $token,
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
