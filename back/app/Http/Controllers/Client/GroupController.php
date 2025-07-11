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
        $query = Group::whereClient(auth()->id())
            ->withCount('lessons', 'clientGroups')
            ->latest('id');

        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, GroupListResource::class);
    }
}
