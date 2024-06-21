<?php

namespace App\Http\Controllers\Common;

use App\Enums\LogType;
use App\Http\Controllers\Controller;
use App\Http\Resources\LogResource;
use App\Models\Log;
use Illuminate\Http\Request;

class LogController extends Controller
{
    protected $filters = [
        'equals' => ['row_id', 'type', 'table']
    ];

    public function index(Request $request)
    {
        $query = Log::query()
            ->with(['entity'])
            ->latest();
        $this->filter($request, $query);
        return $this->handleIndexRequest(
            $request,
            $query,
            LogResource::class
        );
    }

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
