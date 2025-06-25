<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LessonStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\GroupRequest;
use App\Http\Resources\GroupCandidateResource;
use App\Http\Resources\GroupListResource;
use App\Http\Resources\GroupResource;
use App\Http\Resources\GroupVisitResource;
use App\Models\Client;
use App\Models\ClientGroup;
use App\Models\Group;
use App\Models\Lesson;
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

        if ($request->has('tab_client_id')) {
            return $this->getForClientTab($request, $query);
        }

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

    /**
     * Вкладка "группы" у клиента -> управление группами
     * Тут нужно подгружать процесс исполнения договора, если ученик в группе
     */
    private function getForClientTab(Request $request, $query)
    {
        $client = Client::find($request->tab_client_id);
        $clientPlannedLessons = Lesson::query()
            ->with('group')
            ->whereHas(
                'group.clientGroups.contractVersionProgram.contractVersion.contract',
                fn ($q) => $q
                    ->where('client_id', $client->id)
                    ->where('year', $request->year)
            )
            ->where('status', LessonStatus::planned)
            ->where('is_unplanned', 0)
            ->get();

        $query
            ->with(
                'clientGroups',
                fn ($q) => $q->whereHas(
                    'contractVersionProgram.contractVersion.contract',
                    fn ($q) => $q->where('client_id', $client->id)
                )
            )->with(
                'lessons',
                fn ($q) => $q->where('status', LessonStatus::planned)
            );

        $data = GroupListResource::collection($query->get());

        $groupPlannedLessons = Lesson::query()
            ->whereIn('group_id', $data->collection->pluck('id'))
            ->where('status', LessonStatus::planned)
            ->where('is_unplanned', 0)
            ->get()
            ->groupBy('group_id');

        $data->collection->transform(function ($item) use ($clientPlannedLessons, $groupPlannedLessons) {
            if ($item->swamp) {
                return $item;
            }

            $overlapCount = 0;

            foreach ($groupPlannedLessons[$item->id] as $lesson) {
                $start = $lesson->date_time;
                $end = $lesson->date_time->copy()->addMinutes($item->program->getDuration());

                foreach ($clientPlannedLessons as $other) {
                    $otherStart = $other->date_time;
                    $otherEnd = $other->date_time->copy()->addMinutes($other->group->program->getDuration());

                    // Проверка на пересечение
                    if ($start < $otherEnd && $end > $otherStart) {
                        $overlapCount++;
                    }
                }
            }

            $item->overlap_count = $overlapCount;

            return $item;
        });

        return paginate($data);
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
