<?php

namespace App\Http\Controllers\Crm;

use App\Enums\LogType;
use App\Http\Controllers\Controller;
use App\Models\Log;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function store(Request $request)
    {
        Log::create([
            'type' => LogType::view,
            'data' => [
                'url' => $request->url,
            ]
        ]);
    }
}
