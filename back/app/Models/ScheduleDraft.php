<?php

namespace App\Models;

use App\Enums\LessonStatus;
use App\Http\Resources\PersonResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleDraft extends Model
{
    protected $fillable = [
        'data', 'client_id', 'user_id', 'year',
    ];

    protected $casts = [
        'data' => 'collection',
    ];

    public static function fromRam(int $userId): ?ScheduleDraft
    {
        return cache(self::getCacheKey($userId));
    }

    private static function getCacheKey(int $userId): string
    {
        return sprintf('schedule-draft-%d', $userId);
    }

    /**
     * Получить пустой черновик по клиенту
     * Исходное состояние, когда первый раз открываем
     */
    public static function fromActualContracts(Client $client, int $year): ScheduleDraft
    {
        $contracts = $client->contracts()
            ->where('year', $year)
            ->get();

        $data = collect();
        foreach ($contracts as $contract) {
            foreach ($contract->active_version->programs as $p) {
                $data->push((object) [
                    'id' => $p->id,
                    'program' => $p->program->value,
                    'group_id' => $p->clientGroup?->group_id,
                ]);
            }
        }

        return new ScheduleDraft([
            'client_id' => $client->id,
            'data' => $data,
            'year' => $year,
        ]);
    }

    public function addToGroup(int $programId, int $groupId)
    {
        $this->data = $this->data->map(function ($item) use ($programId, $groupId) {
            if ($item['id'] === $programId) {
                $item['group_id'] = $groupId;
            }

            return $item;
        });
    }

    public function removeFromGroup(int $programId)
    {
        $this->data = $this->data->map(function ($item) use ($programId) {
            if ($item['id'] === $programId) {
                $item['group_id'] = null;
            }

            return $item;
        });
    }

    /**
     * Сохранить текущий черновик в оперативную память
     */
    public function toRam(): bool
    {
        return cache()->put(self::getCacheKey($this->user_id), $this, now()->addDay());
    }

    public function getData()
    {
        $year = $this->year;
        $programs = collect($this->data)->map(fn ($p) => (object) $p);

        $contractVersionPrograms = ContractVersionProgram::query()
            ->with('contractVersion.contract')
            ->whereIn('id', $programs->where('id', '>', 0)->pluck('id'))
            ->get()
            ->keyBy('id');

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
            ->withCount('clientGroups')
            ->whereIn('program', $programs->pluck('program')->unique())
            ->with(
                'lessons',
                fn ($q) => $q->where('status', LessonStatus::planned)
            )
            ->get();

        $swamps = $programs->map(function ($p) use ($contractVersionPrograms) {
            $cvp = $p->id > 0 ? $contractVersionPrograms[$p->id] : null;
            $lessonsConducted = $cvp ? $cvp->lessons_conducted : 0;
            $totalLessons = $cvp ? $cvp->total_lessons : 33; // 33 – прогноз по занятиям, чтобы статус был "к исполнению"
            $hasGroup = $p->group_id !== null;

            return (object) [
                'id' => $p->id,
                'program' => $p->program,
                'contract' => $cvp ? $cvp->contractVersion->contract : null,
                'lessons_conducted' => $lessonsConducted,
                'total_lessons' => $totalLessons,
                'status' => ContractVersionProgram::getStatus(
                    $lessonsConducted,
                    $totalLessons,
                    $hasGroup,
                ),
            ];
        })->keyBy('id');

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

            // есть процесс по договору
            if ($p) {
                $isProgramUsed = true;
                $group->swamp = $swamps[$p->id];
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
            $item = $p;
            $swamp = $swamps[$p->id];
            $item->swamp = $swamp;
            $item->contract = $swamp->contract;
            $item->groups = $groups->has($p->program) ? $groups[$p->program] : [];
            $result[] = $item;
        }

        return $result;
    }

    /**
     * Добавить (или удалить) новые программы в проект договора
     *
     * - удалить старые с id < 0, если их нет в $programs;
     * - добавить новые с id < 0, если их нет в $programs;
     * - оставить всё остальное как есть.
     */
    public function newPrograms(array $newPrograms)
    {
        $newPrograms = collect($newPrograms);

        // убираем удалённые
        $data = $this->data
            ->filter(fn ($e) => $e['id'] > 0 || $newPrograms->contains($e['program']))
            ->values();

        // Добавляем недостающие новые программы (id < 0)
        foreach ($newPrograms as $index => $program) {
            if (! $data->contains(fn ($e) => $e['id'] < 0 && $e['program'] === $program)) {
                $data->push([
                    'id' => ($index + 1) * -1,
                    'program' => $program,
                    'group_id' => null,
                ]);
            }
        }

        $this->data = $data;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
