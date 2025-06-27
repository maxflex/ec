<?php

namespace App\Utils;

use App\Enums\LessonStatus;
use App\Http\Resources\PersonResource;
use App\Models\Client;
use App\Models\ClientGroup;
use App\Models\ContractVersionProgram;
use App\Models\Group;
use App\Models\Lesson;
use Illuminate\Support\Collection;

readonly class SwampEditor
{
    private Collection $allPrograms;

    private Collection $programs;

    public function __construct(
        public Client $client,
        public int $year,
        array $programs
    ) {
        $this->programs = collect($programs)->map(fn ($p) => (object) $p);
        $this->allPrograms = $this->programs
            ->map(fn ($p) => $p->program)
            ->unique();
    }

    public function getData()
    {
        // Все планируемые занятия ученика, сгруппированные по дате
        $clientLessons = Lesson::query()
            ->with('group')
            ->whereHas(
                'group.clientGroups.contractVersionProgram.contractVersion.contract',
                fn ($q) => $q
                    ->where('client_id', $this->client->id)
                    ->where('year', $this->year)
            )
            ->where('status', LessonStatus::planned)
            ->where('is_unplanned', 0)
            ->get()
            ->groupBy('date');

        $usedPrograms = ClientGroup::query()
            ->whereHas(
                'contractVersionProgram.contractVersion.contract',
                fn ($q) => $q->where('client_id', $this->client->id)
            )
            ->with('contractVersionProgram')
            ->get()
            ->map(fn ($e) => $e->contractVersionProgram->program)
            ->unique();

        $groups = Group::query()
            ->where('year', $this->year)
            ->whereIn('program', $this->allPrograms)
            ->with(
                'clientGroups',
                fn ($q) => $q->whereHas(
                    'contractVersionProgram.contractVersion.contract',
                    fn ($q) => $q->where('client_id', $this->client->id)
                )
            )
            ->with(
                'lessons',
                fn ($q) => $q->where('status', LessonStatus::planned)
            )
            ->get()
            ->sortBy(['id', 'program'])
            ->values();

        $groupLessons = Lesson::query()
            ->whereIn('group_id', $groups->pluck('id'))
            ->where('status', LessonStatus::planned)
            ->where('is_unplanned', 0)
            ->get()
            ->groupBy('group_id');

        // добавляем кол-во пересечений в каждую группу
        foreach ($groups as $group) {
            if ($group->clientGroups->count() === 1) {
                $clientGroup = $group->clientGroups->first();
                $group->swamp = extract_fields($clientGroup->contractVersionProgram, [
                    'status', 'program', 'total_lessons', 'lessons_conducted',
                ], [
                    'id' => $clientGroup->id,
                ]);
            }

            // если ученик уже в группе, кол-во пересечений не считаем
            // if ($group->swamp) {
            //     return $item;
            // }

            $group->is_program_used = $usedPrograms->contains($group->program);

            $overlap = (object) [
                'count' => 0,
                'programs' => collect(),
            ];

            foreach ($groupLessons[$group->id] as $groupLesson) {
                // не учитываем пересечения с самим собой
                // if ($groupLesson->group_id === $item->id) {
                //     continue;
                // }
                $start = $groupLesson->date_time;
                $end = $groupLesson->date_time->copy()->addMinutes($group->program->getDuration());

                // Берем занятия клиента только с той же датой
                $clientLessonsAtDate = $clientLessons[$groupLesson->date] ?? collect();

                if ($group->is_program_used) {
                    $clientLessonsAtDate = $clientLessonsAtDate->filter(fn ($l) => $l->group->program !== $group->program);
                }

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

            $group->overlap = $overlap;
        }

        $result = [];
        $groups = $groups
            ->map(fn ($g) => extract_fields($g, [
                'program', 'client_groups_count', 'zoom', 'lessons_planned',
                'teacher_counts', 'lesson_counts', 'first_lesson_date', 'swamp',
                'overlap',
            ], [
                'teachers' => PersonResource::collection($g->teachers),
            ]))
            ->groupBy('program');
        foreach ($this->programs as $p) {
            $cvp = $p->id > 0 ? ContractVersionProgram::find($p->id) : null;
            $item = (object) [
                'contract' => $cvp ? $cvp->contractVersion->contract : null,
                'swamp' => $cvp ? extract_fields($cvp, [
                    'status', 'total_lessons', 'lessons_conducted',
                ]) : null,
                'groups' => [],
            ];
            $item->groups = $groups->has($p->program) ? $groups[$p->program]->all() : [];
            $result[$p->id] = $item;
        }

        return $result;
    }
}
