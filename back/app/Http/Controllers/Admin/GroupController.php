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
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    protected $filters = [
        'equals' => ['year'],
        'teacher' => ['teacher_id'],
        'client' => ['client_id'],
        'findInSet' => ['program'],
    ];

    public function index(Request $request)
    {
        if ($request->has('client_id') && $request->has('available_years')) {
            return $this->getAvailableYearsForClient($request->client_id);
        }

        $query = Group::query()
            ->withCount('clientGroups')
            ->with('lessons')
            ->latest('id');
        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, GroupListResource::class);
    }

    /**
     * Доступные годы в расписании ученика
     * Годы из всех групп, где есть сейчас + из всех занятий
     */
    private function getAvailableYearsForClient($clientId)
    {
        return collect(DB::select("
            select distinct(c.year) from client_lessons cl
            join contract_version_programs cvp on cvp.id = cl.contract_version_program_id
            join contract_versions cv on cv.id = cvp.contract_version_id
            join contracts c on c.id = cv.contract_id
            where c.client_id = $clientId
            union
            select distinct(c.year) from `client_groups` cg
            join contract_version_programs cvp on cvp.id = cg.contract_version_program_id
            join contract_versions cv on cv.id = cvp.contract_version_id
            join contracts c on c.id = cv.contract_id
            where c.client_id = $clientId
        "))->pluck('year')->unique()->sortDesc()->values()->all();
    }

    public function show(Group $group)
    {
        return new GroupResource($group);
    }

    public function store(GroupRequest $request)
    {
        $group = auth()->user()->groups()->create($request->all());

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
            $group->lessons()->orderByRaw('date, time')->get()
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
                'contract_id' => $contractId,
            ]);
        }
    }

    protected function filterTeacher($query, $teacherId)
    {
        $query->whereTeacher($teacherId);
    }

    protected function filterClient($query, $clientId)
    {
        $query->whereClient($clientId);
    }
}
