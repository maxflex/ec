<?php

namespace App\Http\Controllers\Crm;

use App\Enums\LogType;
use App\Http\Resources\UserResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequest;
use App\Http\Requests\PreviewRequest;
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
        return new UserResource($phone);
    }

    public function verifyCode(AuthRequest $request)
    {
        $phone = Phone::auth($request->phone);
        $this->logSuccess($phone);
        return [
            'user' => new UserResource($phone),
            'token' => $phone->createSessionToken(),
        ];
    }

    public function user()
    {
        return new UserResource(auth()->user());
    }

    public function preview(PreviewRequest $request)
    {
        $phone = Phone::query()
            ->where('entity_id', $request->id)
            ->where('entity_type', $request->entity_type)
            ->first();
        return [
            'user' => new UserResource($phone),
            'token' => $phone->createSessionToken(),
        ];
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
