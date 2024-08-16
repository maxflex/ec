<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupRequest;
use App\Http\Resources\GroupCandidateResource;
use App\Http\Resources\GroupListResource;
use App\Http\Resources\GroupResource;
use App\Http\Resources\GroupVisitResource;
use App\Models\ClientGroup;
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
            ->withCount('lessons', 'clientGroups')
            ->orderBy('id', 'desc');
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, GroupListResource::class);
    }

    public function show(Group $group)
    {
        return new GroupResource($group);
    }

    public function store(GroupRequest $request)
    {
        $group = auth()->user()->entity->groups()->create($request->all());
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
            ClientGroup::create([
                'group_id' => $group->id,
                'contract_id' => $contractId
            ]);
        }
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
