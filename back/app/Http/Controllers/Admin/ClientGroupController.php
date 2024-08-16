<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientGroupResource;
use App\Models\ClientGroup;
use Illuminate\Http\Request;

class ClientGroupController extends Controller
{
    protected $filters = [
        'equals' => ['group_id']
    ];

    public function index(Request $request)
    {
        $query = ClientGroup::with('contractVersionProgram.contractVersion.contract.client.photo');
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, ClientGroupResource::class);
    }

    public function store(Request $request)
    {
        $clientGroup = ClientGroup::create($request->all());
        return new ClientGroupResource($clientGroup);
    }

    public function destroy(ClientGroup $clientGroup)
    {
        $clientGroup->delete();
    }
}
