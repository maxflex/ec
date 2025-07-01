<?php

namespace App\Models;

use App\Enums\LessonStatus;
use App\Http\Resources\PersonResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleDraft extends Model
{
    protected $fillable = [
        'data', 'user_id', 'year',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Данные для страницы
     */
    public static function getData(
        Client $client,
        int $year,
        array $programs
    ) {
        $programs = collect($programs)->map(fn ($p) => (object) $p);
        $allPrograms = $programs->map(fn ($p) => $p->program)->unique();

        // Все планируемые занятия ученика, сгруппированные по дате
        $clientLessons = Lesson::query()
            ->with('group')
            ->whereHas(
                'group.clientGroups.contractVersionProgram.contractVersion.contract',
                fn ($q) => $q
                    ->where('client_id', $client->id)
                    ->where('year', $year)
            )
            ->where('status', LessonStatus::planned)
            ->where('is_unplanned', 0)
            ->get()
            ->groupBy('date');

        $groups = Group::query()
            ->where('year', $year)
            ->whereIn('program', $allPrograms)
            ->with(
                'clientGroups',
                fn ($q) => $q->whereHas(
                    'contractVersionProgram.contractVersion.contract',
                    fn ($q) => $q->where('client_id', $client->id)
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

        // добавляем информацию о пересечениях и процессе по договору в каждую группу
        foreach ($groups as $group) {
            $isProgramUsed = false;
            if ($group->clientGroups->count() === 1) {
                $isProgramUsed = true;
                $clientGroup = $group->clientGroups->first();
                $group->swamp = extract_fields($clientGroup->contractVersionProgram, [
                    'status', 'program', 'total_lessons', 'lessons_conducted',
                ]);
            }

            // если ученик уже в группе, кол-во пересечений не считаем
            // if ($group->swamp) {
            //     return $item;
            // }

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

                if ($isProgramUsed) {
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
        foreach ($programs as $p) {
            $item = (object) [];
            $cvp = $p->id > 0 ? ContractVersionProgram::find($p->id) : null;
            if ($cvp) {
                $item->contract = $cvp->contractVersion->contract;
                $item->swamp = extract_fields($cvp, [
                    'status', 'total_lessons', 'lessons_conducted',
                ], [
                    'group_id' => $cvp->clientGroup?->group_id,
                ]);
            }
            $item->groups = $groups->has($p->program) ? $groups[$p->program]->all() : [];
            $result[$p->id] = $item;
        }

        return $result;
    }

    public function loadDraft()
    {
        $year = $this->year;
        $programs = collect($this->data)->map(fn ($p) => (object) $p);

        $groupIds = $programs->whereNotNull('group_id')->pluck('group_id')->unique();

        // Все планируемые занятия ученика, сгруппированные по дате
        $clientLessons = Lesson::query()
            ->with('group')
            ->whereIn('group_id', $groupIds)
            ->where('status', LessonStatus::planned)
            ->where('is_unplanned', 0)
            ->get()
            ->groupBy('date');

        $groups = Group::query()
            ->where('year', $year)
            ->whereIn('program', $programs->pluck('program')->unique())
            ->with(
                'lessons',
                fn ($q) => $q->where('status', LessonStatus::planned)
            )
            ->get();

        $groupLessons = Lesson::query()
            ->whereIn('group_id', $groups->pluck('id'))
            ->where('status', LessonStatus::planned)
            ->where('is_unplanned', 0)
            ->get()
            ->groupBy('group_id');

        // добавляем информацию о пересечениях и процессе по договору в каждую группу
        foreach ($groups as $group) {
            $isProgramUsed = false;
            $p = $programs->where('group_id', $group->id)->first();

            // процесс по договору
            if ($p) {
                $isProgramUsed = true;
                // cvp_id 10 ==> group_id 233
                if ($p->id > 0) {
                    $cvp = ContractVersionProgram::find($p->id);
                    $swamp = [
                        'program' => $p->program,
                        'total_lessons' => $cvp->total_lessons,
                        'lessons_conducted' => $cvp->lessons_conducted,
                        'status' => ContractVersionProgram::getStatus(
                            $cvp->total_lessons,
                            $cvp->lessons_conducted,
                            true,
                        ),
                    ];
                } else {
                    $swamp = [
                        'program' => $p->program,
                        'total_lessons' => 0,
                        'lessons_conducted' => 0,
                        'status' => ContractVersionProgram::getStatus(0, 0, true),
                    ];
                }
                $group->swamp = $swamp;
            }

            // если ученик уже в группе, кол-во пересечений не считаем
            // if ($group->swamp) {
            //     return $item;
            // }

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

                if ($isProgramUsed) {
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
        foreach ($programs as $p) {
            $item = (object) [];
            $cvp = $p->id > 0 ? ContractVersionProgram::find($p->id) : null;
            if ($cvp) {
                $item->contract = $cvp->contractVersion->contract;
            }
            $item->groups = $groups->has($p->program) ? $groups[$p->program] : [];
            if (count($item->groups)) {
                $item->swamp = $item->groups->whereNotNull('swamp')->first();
            }
            $result[$p->id] = $item;
        }

        return $result;
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
