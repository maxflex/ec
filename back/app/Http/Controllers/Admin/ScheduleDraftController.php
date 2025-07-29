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
     * Создать версию договора на основе проекта
     */
    public function fillContract(Request $request)
    {
        // если ID не передан – создаём из RAM, тогда нам нужно знать
        // к какому договору из RAM будем создавать
        $request->validate([
            'id' => ['sometimes', 'exists:schedule_drafts,id'],
            'contract_id' => ['sometimes', 'exists:contracts,id'],
        ]);

        if ($request->has('id')) {
            $scheduleDraft = ScheduleDraft::find($request->id);
        } else {
            $scheduleDraft = ScheduleDraft::fromRam(auth()->id());
            $scheduleDraft->contract_id = intval($request->contract_id);
        }

        return $scheduleDraft->fillContract();
        // return [
        //     'scheduleDraft' => new SavedScheduleDraftResource($scheduleDraft),
        //     'contractVersion' => $scheduleDraft->fillContract($contract),
        // ];
    }

    /**
     * Загрузить изменения по договорам
     * (для вкладок у клиента)
     */
    public function loadChanges(Request $request)
    {
        $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
        ]);

        $client = Client::find($request->client_id);

        $result = [];

        foreach ($client->contracts as $contract) {
            $scheduleDrafts = $client->scheduleDrafts
                ->where('created_at', '>', $contract->active_version->created_at)
                ->sortByDesc('created_at')
                ->values();

            foreach ($scheduleDrafts as $scheduleDraft) {
                $changes = (array) $scheduleDraft->changes;
                if (isset($changes[$contract->id])) {
                    if (! isset($result[$contract->id])) {
                        $result[$contract->id] = [
                            'schedule_draft_id' => $scheduleDraft->id,
                            'changes_count' => $changes[$contract->id],
                            'items' => [],
                        ];
                    }

                    $result[$contract->id]['items'][] = new SavedScheduleDraftResource($scheduleDraft);
                }
            }
        }

        // новые договоры
        foreach ($client->scheduleDrafts as $scheduleDraft) {
            $changes = (array) $scheduleDraft->changes;
            if (isset($changes[-1])) {
                if (! isset($result[-1])) {
                    $result[-1] = [
                        'schedule_draft_id' => $scheduleDraft->id,
                        'changes_count' => $changes[-1],
                        'items' => [],
                    ];
                }

                $result[-1]['items'][] = new SavedScheduleDraftResource($scheduleDraft);
            }
        }

        return $result;
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
