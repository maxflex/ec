<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SavedScheduleDraftResource;
use App\Models\Client;
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
            $scheduleDraft->unpack();
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
        return ScheduleDraft::fromRam(auth()->id())->getSchedule();
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

    public function save(Request $request)
    {
        $request->validate([
            'contract_id' => ['required', 'numeric'],
        ]);

        $contractId = intval($request->contract_id);
        $scheduleDraft = ScheduleDraft::fromRam(auth()->id());

        return new SavedScheduleDraftResource(
            $scheduleDraft->saveDraft($contractId)
        );
    }

    /**
     * Загрузить сохранённый проект
     */
    public function show(ScheduleDraft $scheduleDraft)
    {
        return new SavedScheduleDraftResource($scheduleDraft);
    }

    /**
     * Загрузить проект (из Editor)
     */
    public function load(ScheduleDraft $scheduleDraft)
    {
        $fromRam = ScheduleDraft::fromRam(auth()->id());

        $fromRam->insertPrograms($scheduleDraft);
        $fromRam->toRam();

        return $fromRam->getData();
    }

    /**
     * Создать версию договора на основе проекта
     */
    public function fillContract(Request $request)
    {
        // если ID не передан – создаём из RAM, тогда нам нужно знать
        // к какому договору из RAM будем создавать
        $request->validate([
            'id' => ['sometimes', 'exists:schedule_drafts,id'],
            'contract_id' => ['sometimes', 'numeric'],
        ]);

        if ($request->has('id')) {
            $scheduleDraft = ScheduleDraft::find($request->id);
        } else {
            $contractId = intval($request->contract_id);
            $scheduleDraft = ScheduleDraft::fromRam(auth()->id());
            $scheduleDraft->contract_id = $contractId < 0 ? null : $contractId;
        }

        return $scheduleDraft->fillContract();
    }

    /**
     * Удалить сохранённый проект
     */
    public function destroy(ScheduleDraft $scheduleDraft)
    {
        $scheduleDraft->delete();
    }
}
