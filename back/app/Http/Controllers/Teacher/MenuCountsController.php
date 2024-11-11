<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Lesson;

class MenuCountsController extends Controller
{
    public function __invoke()
    {
        return [
            'schedule' => Lesson::query()
                ->where('teacher_id', auth()->id())
                ->needConduct()
                ->exists() ? 1 : 0,
        ];
    }
}
