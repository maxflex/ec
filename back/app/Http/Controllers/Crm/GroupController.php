<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Http\Resources\GroupResource;
use App\Models\Client;
use App\Models\Contract;
use App\Models\ContractGroup;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    protected $filters = [
        'equals' => ['program'],
    ];

    public function index(Request $request)
    {
        $query = Group::query()
            ->with('teacher')
            ->orderBy('id', 'desc');
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query);
    }

    public function show($id)
    {
        $group = Group::find($id);
        return new GroupResource($group);
    }

    public function addClient(Request $request)
    {
        $group = Group::find($request->group_id);
        $client = Client::find($request->client_id);

        foreach ($client->contracts as $contract) {
            foreach ($contract->versions[0]->programs as $p) {
                if ($p->program === $group->program) {
                    return ContractGroup::create([
                        'group_id' => $group->id,
                        'contract_id' => $contract->id
                    ]);
                }
            }
        }
    }

    public function removeContract(Request $request)
    {
        ContractGroup::where($request->all())->delete();
    }
}
