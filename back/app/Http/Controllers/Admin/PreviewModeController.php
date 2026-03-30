<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuthResource;
use App\Models\Client;
use App\Models\Phone;
use App\Models\Representative;
use App\Models\Teacher;
use App\Models\User;
use App\Utils\Session;
use Illuminate\Http\Request;

class PreviewModeController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'phone_id' => ['required', 'exists:phones,id'],
        ]);

        $phone = Phone::findOrFail($request->phone_id);

        // В режиме просмотра разрешаем вход для всех типов личных кабинетов, включая админов.
        abort_unless(in_array($phone->entity_type, [
            Client::class,
            Representative::class,
            Teacher::class,
            User::class,
        ]), 422);

        $token = Session::logIn($phone);

        return [
            'user' => new AuthResource($phone->entity),
            'token' => $token,
        ];
    }
}
