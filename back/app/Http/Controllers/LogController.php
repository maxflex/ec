<?php

namespace App\Http\Controllers;

use App\Enums\LogType;
use App\Http\Resources\LogResource;
use App\Models\Log;
use Illuminate\Http\Request;

class LogController extends Controller
{
    protected $filters = [
        'equals' => ['type', 'table', 'entity_id', 'entity_type', 'device'],
        'rowId' => ['row_id'],
        'q' => ['q'],
    ];

    protected $mapFilters = [
        'table' => '`table`',
    ];

    public function index(Request $request)
    {
        $query = Log::query()
            ->with('entity')
            ->latest();

        $this->filter($request, $query);

        return $this->handleIndexRequest(
            $request,
            $query,
            LogResource::class
        );
    }

    /**
     * Логирование просмотров URL
     */
    public function store(Request $request)
    {
        Log::create([
            'type' => LogType::view,
            'data' => [
                'url' => $request->url,
            ],
        ]);
    }

    protected function filterQ($query, $q)
    {
        if ($q) {
            return $query->where('data', 'like', '%'.$q.'%');
        }
    }

    protected function filterRowId($query, $rowId)
    {
        if ($rowId) {
            return $query->where('row_id', $rowId);
        }
    }
}
