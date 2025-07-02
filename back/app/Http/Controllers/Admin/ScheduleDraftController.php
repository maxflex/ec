<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ScheduleDraft;
use Illuminate\Http\Request;

class ScheduleDraftController extends Controller
{
    /**
     * Вкладка управление группами у клиента
     */
    public function getInitial(Request $request)
    {
        $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'year' => ['required', 'numeric'],
        ]);

        $client = Client::find($request->client_id);
        $year = intval($request->year);

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
        $scheduleDraft->addToGroup(
            intval($request->program_id),
            intval($request->group_id)
        );
        $scheduleDraft->toRam();

        return $scheduleDraft->getData();
    }

    public function newPrograms(Request $request)
    {
        $request->validate([
            'new_programs' => ['required', 'array'],
        ]);

        $scheduleDraft = ScheduleDraft::fromRam(auth()->id());
        $scheduleDraft->newPrograms($request->new_programs);
        $scheduleDraft->toRam();

        return $scheduleDraft->getData();
    }

    public function save(Request $request)
    {
        $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'year' => ['required', 'numeric'],
            'data' => ['required', 'array'],
        ]);

        $client = Client::find($request->client_id);

        return $client->scheduleDrafts()->create([
            'data' => $request->data,
            'year' => $request->year,
            'user_id' => auth()->id(),
        ]);
    }
}
