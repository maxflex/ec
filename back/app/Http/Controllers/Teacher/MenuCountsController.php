<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\Report;

class MenuCountsController extends Controller
{
    public function __invoke()
    {
        return [
            'reports' => Report::query()
                ->where('teacher_id', auth()->id())
                ->where('status', ReportStatus::refused)
                ->exists() ? 1 : 0,
        ];
    }
}
