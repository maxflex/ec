<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupRequest;
use App\Http\Resources\GroupCandidateResource;
use App\Http\Resources\GroupListResource;
use App\Http\Resources\GroupResource;
use App\Http\Resources\GroupVisitResource;
use App\Models\Client;
use App\Models\ContractGroup;
use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    protected $filters = [
        'equals' => ['program', 'year'],
        'teacher' => ['teacher_id'],
        'client' => ['client_id'],
    ];

    public function index(Request $request)
    {
        $query = Group::query()
            ->withCount('lessons')
            ->orderBy('id', 'desc');
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, GroupListResource::class);
    }

    public function show($id)
    {
        $group = Group::find($id);
        return new GroupResource($group);
    }

    public function store(GroupRequest $request)
    {
        $group = Group::create($request->all());
        return new GroupListResource($group);
    }

    public function update(Group $group, GroupRequest $request)
    {
        $group->update($request->all());
        return new GroupResource($group);
    }

    public function destroy(Group $group)
    {
        $group->delete();
    }

    public function visits(Group $group)
    {
        return GroupVisitResource::collection(
            $group->lessons
        );
    }

    public function addClient(Request $request)
    {
        $group = Group::find($request->group_id);
        $client = Client::find($request->client_id);

        foreach ($client->contracts as $contract) {
            foreach ($contract->versions->last()->programs as $p) {
                if ($p->program === $group->program) {
                    return ContractGroup::create([
                        'group_id' => $group->id,
                        'contract_id' => $contract->id
                    ]);
                }
            }
        }
    }

    /**
     * Нажимаем "добавить ученика в текущую группу"
     * Получаем список кандидатов
     */
    public function candidates(Group $group)
    {
        return GroupCandidateResource::collection(
            $group->getCandidates()
        );
    }

    public function bulkStoreCandidates(Group $group, Request $request)
    {
        foreach ($request->ids as $contractId) {
            ContractGroup::create([
                'group_id' => $group->id,
                'contract_id' => $contractId
            ]);
        }
    }

    public function removeContract(Request $request)
    {
        ContractGroup::where($request->all())->delete();
    }

    protected function filterTeacher(&$query, $teacherId)
    {
        $query->whereTeacher($teacherId);
    }

    protected function filterClient(&$query, $clientId)
    {
        $query->whereClient($clientId);
    }
}
