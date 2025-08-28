<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CvpStatus;
use App\Enums\LessonStatus;
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

        if ($request->has('by_program')) {
            return $this->byProgram($query);
        }

        if ($request->has('add_to_group')) {
            return $this->addToGroup($query, $request);
        }

        return $this->handleIndexRequest($request, $query, SwampListResource::class);
    }

    private function counts($query)
    {
        $data = $query->get()->groupBy('client_id');
        $result = collect();
        foreach ($data as $clientId => $d) {
            $counts = [];
            foreach (CvpStatus::cases() as $status) {
                $inGroup = 0;
                $noGroup = 0;
                foreach ($d->where('status', $status->value)->values() as $e) {
                    if ($e->clientGroup) {
                        $inGroup++;
                    } else {
                        $noGroup++;
                    }
                }

                $counts[$status->value.'_in_group'] = $inGroup;
                $counts[$status->value.'_no_group'] = $noGroup;
            }
            $result->push([
                'client' => new PersonResource(Client::find($clientId)),
                'counts' => $counts,
            ]);
        }

        return paginate($result);
    }

    /**
     * Болота по программе /swamps/by-program
     */
    private function byProgram($query)
    {
        $data = $query->get()->groupBy('program');
        $result = collect();
        foreach ($data as $program => $d) {
            $counts = [];
            foreach (CvpStatus::cases() as $status) {
                $inGroup = 0;
                $noGroup = 0;
                foreach ($d->where('status', $status->value)->values() as $e) {
                    if ($e->clientGroup) {
                        $inGroup++;
                    } else {
                        $noGroup++;
                    }
                }

                $counts[$status->value.'_in_group'] = $inGroup;
                $counts[$status->value.'_no_group'] = $noGroup;
            }
            $result->push([
                'program' => $program,
                'counts' => $counts,
            ]);
        }

        return paginate($result);
    }

    private function addToGroup($query, Request $request)
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

        $data = $query->get();

        foreach ($data as $item) {
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
                // не учитываем пересечения с самим с собой
                ->where('group_id', '<>', $group->id)
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

            // если ученик не добавлен в группу по текущей программе, нужно проверить
            // учится ли он по этой программе в другой цепи договора
            // (чтобы нельзя было добавлять на фронте по одной и той же программе)
            $currentContractId = $item->clientGroup
                ? $item->contractVersion->contract_id
                : $data
                    ->where('program', $item->program)
                    ->where('client_id', $client->id)
                    ->where('id', '<>', $item->id)
                    ->whereNotNull('contract_id')
                    ->first()?->contract_id;

            $result->push(extract_fields($item, [
                'total_lessons', 'lessons_conducted', 'status',
            ], [
                'client' => new PersonResource($client),

                'is_risk' => $client->is_risk,

                'uncunducted_count' => $uncunductedCount,

                'teeth' => $client->getSavedSchedule($group->year),

                'overlap' => $overlap,

                // просто contract_id текущей программы
                'contract_id' => $item->contractVersion->contract_id,

                // добавлен ли ученик в группу по текущей программе
                'group_id' => $item->clientGroup?->group_id,

                'current_contract_id' => $currentContractId,

                'has_problems' => $currentContractId !== null,
            ]));
        }

        // сначала идут те, кого можно добавить в группу
        $result = $result->sortBy('has_problems')->values();

        return paginate($result);
    }
}
