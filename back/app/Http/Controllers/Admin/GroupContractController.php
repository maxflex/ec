<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\GroupContractResource;
use App\Models\GroupContract;
use Illuminate\Http\Request;

class GroupContractController extends Controller
{
    protected $filters = [
        'equals' => ['group_id']
    ];

    public function index(Request $request)
    {
        $query = GroupContract::with('contract.client.photo');
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, GroupContractResource::class);
    }

    public function store(Request $request)
    {
        $groupContract = GroupContract::create($request->all());
        return new GroupContractResource($groupContract);
    }

    public function destroy(GroupContract $groupContract)
    {
        $groupContract->delete();
    }
}
