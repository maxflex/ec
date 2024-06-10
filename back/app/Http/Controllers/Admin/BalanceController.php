<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    public function teacher(Teacher $teacher, Request $request)
    {
        $request->validate(['year' => ['required']]);
        return $teacher->getBalance($request->year);
    }
}
