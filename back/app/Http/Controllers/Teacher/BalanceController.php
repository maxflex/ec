<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'year' => ['required', 'numeric', 'min:2015'],
            'teacher_id' => ['required', 'numeric', 'in:' . auth()->id()]
        ]);

        return auth()->user()->getBalance($request->year);
    }
}
