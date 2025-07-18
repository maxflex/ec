<?php

namespace App\Models;

use App\Contracts\HasTeeth;
use App\Enums\LessonStatus;
use App\Http\Resources\PersonResource;
use App\Utils\Teeth;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class ScheduleDraft extends Model implements HasTeeth
{
    protected $fillable = [
        'data', 'client_id', 'contract_id', 'user_id',
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
     *
     * $contract – к какому договору добавляем проект
     * Если не указан, то новая цепь договора, иначе добавление новой версии к $contract
     */
    public static function fromActualContracts(Client $client, ?Contract $contract = null): ScheduleDraft
    {
        $scheduleDraft = new ScheduleDraft([
            'client_id' => $client->id,
            'contract_id' => $contract?->id,
        ]);

        $contracts = $client->contracts()->where('year', $scheduleDraft->year)->get();

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

        $scheduleDraft->data = $data;

        return $scheduleDraft;
    }

    public function addToGroup(int $programId, int $groupId): bool
    {
        // программа уже добавлена в другую группу
        $wasAdded = $this->data->contains(fn ($e) => $e['id'] === $programId && $e['group_id']);

        if ($wasAdded) {
            return false;
        }

        $this->data = $this->data->map(function ($item) use ($programId, $groupId) {
            if ($item['id'] === $programId) {
                $item['group_id'] = $groupId;
            }

            return $item;
        });

        return true;
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

    /**
     * Применить текущий проект
     */
    public function apply()
    {
        if ($this->programs->where('id', '<', 0)->count()) {
            throw new Exception('Невозможно применить программы из "проекта договора"');
        }

        // сначала удаляем все группы клиента
        // возможно в будущем нужно будет удалять не по ID, а всё по году
        $clientGroups = ClientGroup::whereIn('contract_version_program_id', $this->programs->pluck('id'))->get();
        $clientGroups->each->delete();

        $programs = $this->programs
            // применяем только те, где установлен group_id
            ->whereNotNull('group_id')
            // "проект договора" пока исключаем
            ->where('id', '>', 0)
            ->values();

        foreach ($programs as $p) {
            ClientGroup::create([
                'contract_version_program_id' => $p->id,
                'group_id' => $p->group_id,
            ]);
        }
    }

    public function getData()
    {
        $realProgramIds = $this->programs->where('id', '>', 0)->pluck('id');

        // Все планируемые занятия ученика, сгруппированные по дате
        // НЕ client_lessons, а lessons
        $clientLessons = $this->getLessonsQuery()
            ->with('group')
            ->where('status', LessonStatus::planned)
            ->where('is_unplanned', 0)
            ->get()
            ->groupBy('date');

        $contractVersionPrograms = ContractVersionProgram::query()
            ->with('contractVersion')
            ->whereIn('id', $realProgramIds)
            ->get()
            ->keyBy('id');

        $groups = Group::query()
            ->where('year', $this->year)
            ->withCount('clientGroups')
            ->whereIn('program', $this->programs->pluck('program')->unique())
            ->with('lessons')
            ->get();

        $clientGroups = ClientGroup::query()
            ->whereIn('contract_version_program_id', $realProgramIds)
            ->pluck('group_id', 'contract_version_program_id');

        $swamps = $this->programs->map(function ($p) use ($contractVersionPrograms) {
            $cvp = $p->id > 0 ? $contractVersionPrograms[$p->id] : null;
            $lessonsConducted = $cvp ? $cvp->lessons_conducted : 0;
            $totalLessons = $cvp ? $cvp->total_lessons : 33; // 33 – прогноз по занятиям, чтобы статус был "к исполнению"
            $hasGroup = $p->group_id !== null;

            return (object) [
                'id' => $p->id,
                'program' => $p->program,
                'contract_id' => $cvp ? $cvp->contractVersion->contract_id : $p->contract_id,
                'lessons_conducted' => $lessonsConducted,
                'total_lessons' => $totalLessons,
                'status' => ContractVersionProgram::getStatus(
                    $lessonsConducted,
                    $totalLessons,
                    $hasGroup,
                ),
            ];
        })->keyBy('id');

        // добавляем информацию о пересечениях и процессе по договору в каждую группу
        foreach ($groups as $group) {
            $isProgramUsed = false;
            $p = $this->programs->where('group_id', $group->id)->first();

            $group->draft_status = 0;

            // есть процесс по договору
            if ($p) {
                $isProgramUsed = true;
                $group->swamp = $swamps[$p->id];

                // процесс по договору есть в черновике, но нет в реальности
                if (@$clientGroups[$p->id] !== $group->id) {
                    $group->draft_status = 1;
                }
            } else {
                // процесса по договору нет в черновике, но есть в реальности
                if ($clientGroups->contains($group->id)) {
                    $group->draft_status = 2;
                }
            }

            // если ученик уже в группе, кол-во пересечений не считаем
            // if ($group->swamp) {
            //     return $item;
            // }

            $overlap = (object) [
                'count' => 0,
                'programs' => collect(),
            ];

            foreach ($group->lessons as $groupLesson) {
                if (now()->format('Y-m-d H:i:s') >= $groupLesson->date_time && $groupLesson->status !== LessonStatus::conducted) {
                    @$group->uncunducted_count++;
                }

                if ($groupLesson->status !== LessonStatus::planned || $groupLesson->is_unplanned) {
                    continue;
                }

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

        $groupsByProgram = $groups
            ->map(fn ($g) => extract_fields($g, [
                'program', 'client_groups_count', 'zoom', 'lessons_planned',
                'teacher_counts', 'lesson_counts', 'first_lesson_date', 'swamp',
                'overlap', 'uncunducted_count', 'draft_status',
            ], [
                'teeth' => $g->getTeeth(),
                'teachers' => PersonResource::collection($g->teachers),
            ]))
            ->groupBy('program');

        $result = collect();

        foreach ($this->programs as $p) {
            $swamp = $swamps[$p->id];
            $result->push((object) [
                ...(array) $p,
                'swamp' => $swamp,
                'contract_id' => $swamp?->contract_id,
                'groups' => $groupsByProgram->has($p->program) ? $groupsByProgram[$p->program] : [],
            ]);
        }

        // group result by contract_id
        return $result->groupBy('contract_id')
            ->map(fn ($programs, $contractId) => [
                'contract_id' => $contractId,
                'is_active' => $contractId === $this->contract_id,
                'programs' => $programs->sortBy('id')->values(),
            ])
            ->sortByDesc('is_active')
            ->values();
    }

    private function getLessonsQuery()
    {
        $groupIds = $this->programs->whereNotNull('group_id')->pluck('group_id')->unique();

        return Lesson::query()->whereIn('group_id', $groupIds);
    }

    public function getTeeth(?int $year = null): object
    {
        return Teeth::get($this->getLessonsQuery());
    }

    /**
     * Добавить новые программы в проект договора
     * Добавление всегда в текущий contract_id
     */
    public function newPrograms(array $newPrograms)
    {
        $newPrograms = collect($newPrograms);
        $id = min($this->data->min('id'), 0);
        $data = $this->data;

        // Добавляем новые программы
        foreach ($newPrograms as $program) {
            $id--;
            $data->push([
                'id' => $id, // id < 0
                'contract_id' => $this->contract_id,
                'program' => $program,
                'group_id' => null,
            ]);
        }

        $this->data = $data;
    }

    public function getProgramsAttribute(): Collection
    {
        return once(fn () => collect($this->data)->map(fn ($p) => (object) $p));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Если есть договор (добавляем проект к конкретному договору) – то год из договора
     * Иначе текущий академический год
     */
    public function getYearAttribute(): int
    {
        return $this->contract?->year ?? current_academic_year();
    }
}
