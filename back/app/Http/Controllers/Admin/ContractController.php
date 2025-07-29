<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContractResource;
use App\Models\Client;
use App\Models\Contract;
use App\Models\ContractVersionProgram;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    protected $filters = [
        'equals' => ['client_id', 'year'],
    ];

    public function index(Request $request)
    {
        $query = Contract::with('client')->oldest('id');
        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, ContractResource::class);
    }

    public function show(Contract $contract)
    {
        return new ContractResource($contract);
    }

    /**
     * Новая цепь договора
     */
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'sum' => ['required', 'numeric'],
        ]);
        $client = Client::find($request->client_id);
        $contract = $client->contracts()->create($request->contract);
        $contractVersion = $contract->versions()->create([
            ...$request->all(),
            'user_id' => auth()->id(),
        ]);
        foreach ($request->programs as $p) {
            $contractVersionProgram = $contractVersion->programs()->create($p);
            $contractVersionProgram->prices()->createMany($p['prices']);
        }
        sync_relation($contractVersion, 'payments', $request->all());

        return new ContractResource($contract->fresh());
    }

    public function filterContractVersionProgramId($query, $id)
    {
        $program = ContractVersionProgram::find($id);
        $query->whereHas(
            'versions', fn ($q) => $q
                ->where('is_active', true)
                ->whereHas('programs', fn ($q) => $q->where('program', $program->program))
        )->where('year', $program->contractVersion->contract->year);
    }
}
