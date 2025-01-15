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
        $query = Contract::with('client')->latest('id');
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, ContractResource::class);
    }

    /**
     * Новая цепь договора
     */
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'sum' => ['required', 'numeric']
        ]);
        $contract = Contract::create([
            ...$request->contract,
            'client_id' => $request->client_id,
        ]);
        $contractVersion = auth()->user()->contractVersions()->create([
            ...$request->all(),
            'contract_id' => $contract->id,
        ]);
        $contractVersion->syncRelation($request->all(), 'programs');
        $contractVersion->syncRelation($request->all(), 'payments');
        return new ContractResource($contract->fresh());
    }
}
