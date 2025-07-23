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
        'equals' => ['client_id', 'contract_id'],
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
            'client_id' => ['required', 'exists:clients,id'],
            'year' => ['required', 'numeric'],
        ]);

        $client = Client::find($request->client_id);

        $scheduleDraft = ScheduleDraft::fromActualContracts($client, intval($request->year));
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
            'new_programs' => ['sometimes', 'array'],
        ]);

        $scheduleDraft = ScheduleDraft::fromRam(auth()->id());
        $scheduleDraft->addPrograms($request->new_programs);
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

        return new SavedScheduleDraftResource($scheduleDraft);
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
     * Загрузить сохранённый проект (внутри договора)
     */
    public function load(ScheduleDraft $scheduleDraft)
    {
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
