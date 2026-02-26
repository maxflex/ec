<?php

namespace App\Http\Controllers\Pub;

use App\Enums\LogType;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitPhoneRequest;
use App\Http\Requests\VerifyCodeRequest;
use App\Http\Resources\AuthResource;
use App\Http\Resources\PhoneResource;
use App\Models\Log;
use App\Models\Phone;
use App\Utils\MagicLink;
use App\Utils\Session;
use App\Utils\TelegramMiniApp;
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

        // если "без телеграм", отправляем код в SMS
        if ($phone->telegram_id || $phone->is_telegram_disabled) {
            VerificationService::sendCode($phone, $phone->is_telegram_disabled);
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

        return $this->logIn($phone);
    }

    /**
     * Входим в систему...
     */
    private function logIn(Phone $phone, array $meta = [])
    {
        $viaCallApp = isset($meta['via_call_app']);
        $token = Session::logIn($phone, $viaCallApp);
        $this->logSuccess($phone, $meta);

        return [
            'user' => new AuthResource($phone->entity),
            'phone' => new PhoneResource($phone),
            'token' => $token,
        ];
    }

    private function logSuccess(Phone $phone, array $meta = [])
    {
        Log::create([
            'entity_type' => $phone->entity_type,
            'entity_id' => $phone->entity_id,
            'number' => $phone->number,
            'telegram_id' => $phone->telegram_id,
            'type' => LogType::auth,
            'data' => [
                ...$meta,
                'phone_id' => $phone->id,
                'number' => $phone->number,
            ],
        ]);
    }

    /**
     * Вход через Telegram Mini-App
     */
    public function viaTelegram(Request $request)
    {
        $request->validate([
            'initData' => ['required', 'string'],
        ]);

        $tgMiniApp = new TelegramMiniApp($request->input('initData'));

        abort_unless($tgMiniApp->isSafe(), 422);

        $user = $tgMiniApp->getUser();

        $candidates = Phone::query()
            ->where('telegram_id', $user->id)
            ->get()
            ->filter(
                fn ($phone) => $phone->entity_type::whereId($phone->entity_id)->canLogin()->exists()
            );

        // должен быть в итоге единственный кандидат к логину
        abort_unless($candidates->count() === 1, 422);

        return $this->logIn(
            $candidates->first(),
            ['via_telegram' => true]
        );
    }

    /**
     * Вход через ссылку
     *
     * @DEPRECATED не используется
     */
    public function viaMagicLink(Request $request)
    {
        $request->validate([
            'link' => ['required', 'string'],
        ]);

        if (is_localhost()) {
            sleep(1);
        }

        $phone = MagicLink::get($request->input('link'));

        abort_if(! $phone, 422);
        abort_if(! Phone::auth($phone->number), 422);

        return $this->logIn($phone, [
            'via_magic_link' => true,
        ]);
    }

    /**
     * Вход для CallApp, отличается тем, что токен выдается на год,
     * чтобы не приходилось логиниться постоянно
     */
    public function viaCallApp()
    {
        /** @var Phone $phone */
        $phone = auth()->user()->phone;

        return $this->logIn($phone, [
            'via_call_app' => true,
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
