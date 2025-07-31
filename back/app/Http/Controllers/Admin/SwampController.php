<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LessonStatus;
use App\Enums\SwampStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\PersonResource;
use App\Http\Resources\SwampListResource;
use App\Models\Client;
use App\Models\ContractVersionProgram;
use App\Models\Group;
use App\Models\Lesson;
use Illuminate\Http\Request;

class SwampController extends Controller
{
    protected $filters = [
        'equals' => ['year', 'client_id'],
        'findInSet' => ['program'],
    ];

    public function index(Request $request)
    {
        $query = ContractVersionProgram::with([
            'prices',
            'clientGroup.group',
            'contractVersion.contract.client',
        ])
            ->join('contract_versions as cv', fn ($join) => $join
                ->on('cv.id', '=', 'contract_version_programs.contract_version_id')
                ->where('cv.is_active', true)
            )
            ->join('contracts as c', 'c.id', '=', 'cv.contract_id')
            ->selectRaw('
                contract_version_programs.*,
                c.year,
                c.client_id,
                cv.contract_id
            ');

        $this->filter($request, $query);

        if ($request->has('counts')) {
            return $this->counts($query);
        }

        if ($request->has('students_tab')) {
            return $this->studentsTab($query, $request);
        } else {
            $resource = SwampListResource::class;
        }

        return $this->handleIndexRequest($request, $query, $resource);
    }

    private function counts($query)
    {
        $data = $query->get()->groupBy('client_id');
        $result = collect();
        foreach ($data as $clientId => $d) {
            $counts = [];
            foreach (SwampStatus::cases() as $status) {
                $counts[$status->value] = $d->where('status', $status->value)->count();
            }
            $result->push([
                'client' => new PersonResource(Client::find($clientId)),
                'counts' => $counts,
            ]);
        }

        return paginate($result);
    }

    private function studentsTab($query, Request $request)
    {
        $request->validate([
            'group_id' => ['required', 'exists:groups,id'],
        ]);

        $group = Group::find($request->group_id);
        $result = collect();

        // кол-во непроведённых
        $now = now()->format('Y-m-d H:i:s');
        $uncunductedCount = 0;
        foreach ($group->lessons as $groupLesson) {
            // если урок уже начался, но препод его не провёл – считаем кол-во таких
            if ($now >= $groupLesson->date_time && $groupLesson->status === LessonStatus::planned) {
                $uncunductedCount++;
            }
        }

        foreach ($query->get() as $item) {
            $client = $item->contractVersion->contract->client;
            $cvpIds = $client->getContractVersionProgramIds($group->year);

            $overlap = (object) [
                'count' => 0,
                'programs' => collect(),
            ];

            // Все планируемые занятия ученика, сгруппированные по дате
            // Нужно для подсчёта пересечений
            // (это НЕ client_lessons, а lessons)
            $clientLessonsByDate = Lesson::query()
                ->where(fn ($q) => $q->whereExists(fn ($q) => $q->selectRaw(1)
                    ->from('client_groups as cg')
                    ->whereColumn('cg.group_id', 'lessons.group_id')
                    ->whereIn('cg.contract_version_program_id', $cvpIds)
                ))
                ->with('group')
                ->where('status', LessonStatus::planned)
                ->where('is_unplanned', 0)
                ->get()
                ->groupBy('date');

            foreach ($group->lessons as $groupLesson) {
                if ($groupLesson->status !== LessonStatus::planned || $groupLesson->is_unplanned) {
                    continue;
                }

                $start = $groupLesson->date_time;
                $end = $groupLesson->date_time->copy()->addMinutes($group->program->getDuration());

                // Берем занятия клиента только с той же датой
                $clientLessonsAtDate = $clientLessonsByDate[$groupLesson->date] ?? collect();

                // if ($isProgramUsed) {
                //     $clientLessonsAtDate = $clientLessonsAtDate->filter(fn ($l) => $l->group->program !== $group->program);
                // }

                foreach ($clientLessonsAtDate as $clientLesson) {
                    $program = $clientLesson->group->program;
                    $otherStart = $clientLesson->date_time;
                    $otherEnd = $otherStart->copy()->addMinutes($program->getDuration());

                    if ($start < $otherEnd && $end > $otherStart) {
                        $overlap->count++;
                        if (! $overlap->programs->contains($program)) {
                            $overlap->programs->push($program);
                        }
                    }
                }
            }

            $result->push([
                'id' => $item->id,
                'client' => new PersonResource($client),
                'uncunducted_count' => $uncunductedCount,
                // 'teeth' => $client->getTeeth($group->year),
                'overlap' => $overlap,
                'current_contract_id' => $item->contractVersion->contract_id,
                'group_id' => $item->clientGroup?->group_id,
                'swamp' => extract_fields($item, [
                    'total_lessons', 'lessons_conducted', 'status',
                ]),
            ]);
        }

        return paginate($result);

        // return paginate($result->sortBy(fn ($i)));
    }
}
