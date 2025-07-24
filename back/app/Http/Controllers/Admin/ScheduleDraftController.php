<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SavedScheduleDraftResource;
use App\Models\Client;
use App\Models\Contract;
use App\Models\ScheduleDraft;
use Exception;
use Illuminate\Http\Request;

class ScheduleDraftController extends Controller
{
    protected $filters = [
        'equals' => ['client_id', 'year'],
        'contract' => ['contract_id'],
    ];

    public function index(Request $request)
    {
        $query = ScheduleDraft::latest();

        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, SavedScheduleDraftResource::class);
    }

    /**
     * Вкладка управление группами у клиента
     */
    public function fromActualContracts(Request $request)
    {
        $request->validate([
            'id' => ['sometimes', 'exists:schedule_drafts,id'],
            'client_id' => ['required', 'exists:clients,id'],
            'year' => ['required', 'numeric'],
        ]);

        if ($request->has('id')) {
            $scheduleDraft = ScheduleDraft::find($request->input('id'));
        } else {
            $client = Client::find($request->client_id);
            $scheduleDraft = ScheduleDraft::fromActualContracts($client, intval($request->year));
        }

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

    public function removeProgram(Request $request)
    {
        $request->validate([
            'id' => ['required', 'numeric', 'max:-1'],
        ]);

        $scheduleDraft = ScheduleDraft::fromRam(auth()->id());
        $scheduleDraft->removeProgram(intval($request->id));
        $scheduleDraft->toRam();

        return $scheduleDraft->getData();
    }

    public function addPrograms(Request $request)
    {
        $request->validate([
            'contract_id' => ['required', 'numeric'],
            'new_programs' => ['sometimes', 'array'],
        ]);

        $scheduleDraft = ScheduleDraft::fromRam(auth()->id());
        $scheduleDraft->addPrograms($request->new_programs, intval($request->contract_id));
        $scheduleDraft->toRam();

        return $scheduleDraft->getData();
    }

    public function getTeeth()
    {
        return ScheduleDraft::fromRam(auth()->id())->getTeeth();
    }

    public function applyMoveGroups()
    {
        $scheduleDraft = ScheduleDraft::fromRam(auth()->id());
        try {
            $scheduleDraft->applyMoveGroups();
        } catch (Exception $e) {
            abort(422, $e->getMessage());
        }

        return $scheduleDraft->getData();
    }

    public function save()
    {
        $scheduleDraft = ScheduleDraft::fromRam(auth()->id());
        $scheduleDraft->save();
    }

    /**
     * Загрузить сохранённый проект
     */
    public function show(ScheduleDraft $scheduleDraft)
    {
        return new SavedScheduleDraftResource($scheduleDraft);
    }

    /**
     * Загрузить сохранённый проект (внутри договора)
     */
    public function load(ScheduleDraft $scheduleDraft, Request $request)
    {
        $request->validate([
            'contract_id' => ['sometimes', 'exists:contracts,id'],
        ]);

        $contract = $request->has('contract_id') ? Contract::find($request->contract_id) : null;

        return $scheduleDraft->fillContract($contract);
    }

    /**
     * Удалить сохранённый проект
     */
    public function destroy(ScheduleDraft $scheduleDraft)
    {
        $scheduleDraft->delete();
    }

    protected function filterContract($query, $id)
    {
        $id = intval($id);

        if ($id < 0) {
            $id = -1;
        }

        $query->whereRaw("JSON_CONTAINS(`programs`->>'$[*].contract_id', '$id')");
    }
}
