<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SwampListResource;
use App\Utils\Swamp;
use Illuminate\Http\Request;

class SwampController extends Controller
{
    protected $filters = [
        'equals' => ['year', 'program', 'client_id'],
        'status' => ['status'],
    ];

    public function index(Request $request)
    {
        $query = Swamp::query();
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, SwampListResource::class);
    }

    protected function filterStatus(&$query, $status)
    {
        Swamp::filterStatus($query, $status);
    }
}
