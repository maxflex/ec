<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SwampListResource;
use App\Models\Swamp;
use Illuminate\Http\Request;

class SwampController extends Controller
{
    protected $filters = [
        'equals' => ['year', 'program', 'is_closed'],
        'null' => ['group_id']
    ];

    protected $mapFilters = [
        // 'in_group' => 'contract_version_program_id',
    ];

    public function index(Request $request)
    {
        $query = Swamp::query();
        // $query->where('contract_id', 14740);
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, SwampListResource::class);
    }
}
