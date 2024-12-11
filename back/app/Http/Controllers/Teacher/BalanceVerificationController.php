<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Utils\BalanceVerificationService;
use Illuminate\Http\Request;

class BalanceVerificationController extends Controller
{
    public function check()
    {
        return [
            'seconds' => BalanceVerificationService::check(auth()->user())
        ];
    }

    public function sendCode()
    {
        BalanceVerificationService::sendCode(auth()->user());
    }

    public function checkCode(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string']
        ]);

        return [
            'result' => BalanceVerificationService::verifyCode(
                auth()->user(),
                $request->input('code')
            ),
        ];
    }
}
