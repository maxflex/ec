<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Report;

class MenuCountsController extends Controller
{
    public function __invoke()
    {
        return [
            'reports' => Report::getMenuCounts(auth()->id()),
        ];
    }
}
