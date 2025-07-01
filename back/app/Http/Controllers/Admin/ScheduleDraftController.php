<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Group;
use App\Models\ScheduleDraft;
use Illuminate\Http\Request;

class ScheduleDraftController extends Controller
{
    /**
     * Вкладка управление группами у клиента
     */
    public function getData(Request $request)
    {
        $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'year' => ['required', 'numeric'],
            'programs' => ['required', 'array'],
        ]);

        $client = Client::find($request->client_id);
        $year = intval($request->year);

        return ScheduleDraft::first()->loadDraft();

        // return ScheduleDraft::getData($client, $year, $request->programs);
    }

    public function addToGroup(Request $request)
    {
        $request->validate([
            // может быть -1 для несуществующих программ
            'program' => ['required'],
            'group_id' => ['required', 'exists:groups,id'],
            'client_id' => ['required', 'exists:clients,id'],
        ]);
        $group = Group::find($request->group_id);
        $client = Client::find($request->client_id);

        // ScheduleDraft::addToGroup($group, $client, $request->program);
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
