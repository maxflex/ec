<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuthResource;
use App\Models\{Client, Teacher};
use App\Utils\Session;
use Illuminate\Http\Request;

class PreviewController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'client_id' => ['required_without:teacher_id', 'exists:clients,id'],
            'teacher_id' => ['required_without:client_id', 'exists:teachers,id'],
        ]);

        $user = $request->has('client_id')
            ? Client::find($request->input('client_id'))
            : Teacher::find($request->input('teacher_id'));

        // логинимся под любым номером телефона
        $token = Session::logIn($user->phones()->first());

        return [
            'user' => new AuthResource($user),
            'token' => $token,
        ];
    }
}
