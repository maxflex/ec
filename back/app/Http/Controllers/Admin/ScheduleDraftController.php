<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ScheduleDraftResource;
use App\Models\Client;
use App\Models\Contract;
use App\Models\ScheduleDraft;
use Exception;
use Illuminate\Http\Request;

class ScheduleDraftController extends Controller
{
    protected $filters = [
        'equals' => ['client_id', 'year'],
    ];

    public function index(Request $request)
    {
        $query = ScheduleDraft::latest();

        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, ScheduleDraftResource::class);
    }

    /**
     * Вкладка управление группами у клиента
     */
    public function getInitial(Request $request)
    {
        $request->validate([
            'client_id' => ['required_without:contract_id', 'exists:clients,id'],
            'contract_id' => ['required_without:client_id', 'exists:contracts,id'],
        ]);

        if ($request->has('contract_id')) {
            $contract = Contract::find($request->contract_id);
            $client = $contract->client;
            $year = $contract->year;
        } else {
            $client = Client::find($request->client_id);
            $year = current_academic_year();
        }

        $scheduleDraft = ScheduleDraft::fromActualContracts($client, $year);
        $scheduleDraft->user_id = auth()->id();
        $scheduleDraft->toRam();

        return $scheduleDraft->getData();
    }

    public function removeFromGroup(Request $request)
    {
        $request->validate([
            // может быть -1 для несуществующих программ
            'program_id' => ['required', 'numeric'],
        ]);

        $scheduleDraft = ScheduleDraft::fromRam(auth()->id());
        $scheduleDraft->removeFromGroup(intval($request->program_id));
        $scheduleDraft->toRam();

        return $scheduleDraft->getData();
    }

    public function addToGroup(Request $request)
    {
        $request->validate([
            // может быть -1 для несуществующих программ
            'program_id' => ['required', 'numeric'],
            'group_id' => ['required', 'exists:groups,id'],
        ]);

        $scheduleDraft = ScheduleDraft::fromRam(auth()->id());
        $wasAdded = $scheduleDraft->addToGroup(
            intval($request->program_id),
            intval($request->group_id)
        );

        abort_if(! $wasAdded, 422);

        $scheduleDraft->toRam();

        return $scheduleDraft->getData();
    }

    public function newPrograms(Request $request)
    {
        $request->validate([
            'new_programs' => ['sometimes', 'array'],
        ]);

        $scheduleDraft = ScheduleDraft::fromRam(auth()->id());
        $scheduleDraft->newPrograms($request->new_programs);
        $scheduleDraft->toRam();

        return $scheduleDraft->getData();
    }

    public function getTeeth()
    {
        return ScheduleDraft::fromRam(auth()->id())->getTeeth();
    }

    public function apply()
    {
        $scheduleDraft = ScheduleDraft::fromRam(auth()->id());
        try {
            $scheduleDraft->apply();
        } catch (Exception $e) {
            abort(422, $e->getMessage());
        }

        return $scheduleDraft;
    }

    public function save()
    {
        $scheduleDraft = ScheduleDraft::fromRam(auth()->id());
        $scheduleDraft->save();

        return new ScheduleDraftResource($scheduleDraft);
    }

    /**
     * Загрузить сохранённый проект
     */
    public function show(ScheduleDraft $scheduleDraft)
    {
        $scheduleDraft->user_id = auth()->id();
        $scheduleDraft->toRam();

        return $scheduleDraft->getData();
    }

    /**
     * Удалить сохранённый проект
     */
    public function destroy(ScheduleDraft $scheduleDraft)
    {
        $scheduleDraft->delete();
    }
}
