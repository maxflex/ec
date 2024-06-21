<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    public function teacher(Teacher $teacher, Request $request)
    {
        $request->validate(['year' => ['required']]);
        if ($teacher->id !== auth()->id()) {
            return response(status: 412);
        }
        return $teacher->getBalance($request->year);
    }
}
