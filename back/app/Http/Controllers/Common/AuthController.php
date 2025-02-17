<?php

namespace App\Http\Controllers\Common;

use App\Enums\LogType;
use App\Enums\TelegramTemplate;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitPhoneRequest;
use App\Http\Requests\VerifyCodeRequest;
use App\Http\Resources\AuthResource;
use App\Http\Resources\PhoneResource;
use App\Models\Client;
use App\Models\ClientParent;
use App\Models\Log;
use App\Models\Phone;
use App\Models\TelegramMessage;
use App\Utils\Session;
use App\Utils\VerificationService;
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

    private function logSuccess(Phone $phone)
    {
        // Если это первый вход в систему родителя/ученика,
        // отправляем Telegram с возможностями ЛК
        // if (in_array($phone->entity_type, [Client::class, ClientParent::class])) {
        //     $isFirstLogin = ! Log::where([
        //         'entity_type' => $phone->entity_type,
        //         'entity_id' => $phone->entity_id,
        //         'type' => LogType::auth,
        //     ])->exists();
        //     if ($isFirstLogin) {
        //         TelegramMessage::sendTemplate(
        //             TelegramTemplate::firstLogin,
        //             $phone->entity,
        //         );
        //     }
        // }
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

    public function user()
    {
        return new AuthResource(auth()->user());
    }

    public function logout(Request $request)
    {
        Session::logout($request->bearerToken());
    }
}
