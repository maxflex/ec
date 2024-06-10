<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContractResource;
use App\Models\Contract;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    protected $filters = [
        'equals' => ['client_id']
    ];

    public function index(Request $request)
    {
        $query = Contract::query()
            ->with('client')
            ->orderBy('id', 'desc');
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, ContractResource::class);
    }

    public function store(Request $request)
    {
        $contract = Contract::create($request->all());
        $contractVersion = auth()->user()->entity->contractVersions()->create([
            ...$request->versions[0],
            'contract_id' => $contract->id,
        ]);
        $contractVersion->syncRelation($request->versions[0], 'programs');
        $contractVersion->syncRelation($request->versions[0], 'payments');
        return new ContractResource($contract->fresh());
    }
}
