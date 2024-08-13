<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\GroupListResource;
use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    protected $filters = [
        'equals' => ['program', 'year'],
    ];

    public function index(Request $request)
    {
        $query = Group::query()
            ->whereHas(
                'contracts',
                fn ($q) => $q->where('client_id', auth()->id())
            )
            ->withCount('lessons', 'groupContracts')
            ->orderBy('id', 'desc');

        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, GroupListResource::class);
    }
}
