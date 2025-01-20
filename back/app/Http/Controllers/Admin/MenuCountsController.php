<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use Illuminate\Http\Request;

class MenuCountsController extends Controller
{
    public function __invoke(Request $request)
    {
        return [
            'schedule' => Lesson::query()
                ->whereHas('group', fn($q) => $q->where('year', current_academic_year()))
                ->needConduct()
                ->exists() ? 1 : 0,
        ];
    }
}
