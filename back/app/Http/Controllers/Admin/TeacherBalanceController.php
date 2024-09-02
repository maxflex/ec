<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Utils\TeacherBalance;
use Illuminate\Http\Request;

/**
 * Сводный баланс всех преподов
 * /teacher-balances
 */
class TeacherBalanceController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'year' => ['required', 'numeric', 'min:2015']
        ]);

        return TeacherBalance::getData($request->year);
    }
}