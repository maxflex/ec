<?php

namespace App\Models;

use App\Contracts\HasTeeth;
use App\Enums\LessonStatus;
use App\Http\Resources\ContractVersionResource;
use App\Http\Resources\PersonResource;
use App\Utils\Teeth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class ScheduleDraft extends Model implements HasTeeth
{
    protected $fillable = [
        'programs', 'client_id', 'contract_id', 'user_id',
    ];

    protected $casts = [
        'programs' => 'collection',
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
    public static function fromActualContracts(Client $client, int $year): ScheduleDraft
    {
        $contracts = $client->contracts()->where('year', $year)->get();

        $programs = collect();
        foreach ($contracts as $contract) {
            $programs = $programs->merge(self::getProgramsForContract($contract));
        }

        return new ScheduleDraft([
            'client_id' => $client->id,
            'programs' => $programs,
        ]);
    }

    /**
     * Получить programs для договора
     */
    private static function getProgramsForContract(Contract $contract)
    {
        return $contract->active_version->programs->map(
            fn (ContractVersionProgram $cvp) => (object) [
                'id' => $cvp->id,
                'contract_id' => $contract->id,
                'program' => $cvp->program->value,
                'group_id' => $cvp->clientGroup?->group_id,
            ]);
    }

    public function addToGroup(int $programId, int $groupId): bool
    {
        // программа уже добавлена в другую группу
        $wasAdded = $this->programs->contains(fn ($e) => $e['id'] === $programId && $e['group_id']);

        if ($wasAdded) {
            return false;
        }

        $this->programs = $this->programs->map(function ($item) use ($programId, $groupId) {
            if ($item['id'] === $programId) {
                $item['group_id'] = $groupId;
            }

            return $item;
        });

        return true;
    }

    public function removeFromGroup(int $programId)
    {
        $this->programs = $this->programs->map(function ($item) use ($programId) {
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
     * Применить: новая версия договора
     */
    // private function applyNewContractVersion()
    // {
    //     $programIds = $this->contract->active_version->programs->pluck('id')->flip();
    //
    //     $contractVersion = $this->contract->versions()->create([
    //         ...$this->contract->active_version->toArray(),
    //         'user_id' => auth()->id(),
    //     ]);
    //
    //     foreach ($this->data as $p) {
    //         if ($p['id'] < 0 || $programIds->has($p['id'])) {
    //             // удаляем из группы
    //         }
    //         // $contractVersionProgram = $contractVersion->programs()->create($p);
    //         // $contractVersionProgram->prices()->createMany($p['prices']);
    //     }
    //
    //     // сначала удаляем все группы клиента
    //     // возможно в будущем нужно будет удалять не по ID, а всё по году
    //     $clientGroups = ClientGroup::whereIn('contract_version_program_id', $this->programs->pluck('id'))->get();
    //     $clientGroups->each->delete();
    //
    //     $programs = $this->programs
    //         // применяем только те, где установлен group_id
    //         ->whereNotNull('group_id')
    //         // "проект договора" пока исключаем
    //         ->where('id', '>', 0)
    //         ->values();
    //
    //     foreach ($programs as $p) {
    //         ClientGroup::create([
    //             'contract_version_program_id' => $p->id,
    //             'group_id' => $p->group_id,
    //         ]);
    //     }
    // }
    /**
     * Применить текущий проект
     */
    public function apply()
    {
        $this->contract_id ? $this->applyNewContractVersion() : $this->applyNewContract();
    }

    public function getData()
    {
        $realProgramIds = $this->programs->where('id', '>', 0)->pluck('id');

        // Все планируемые занятия ученика, сгруппированные по дате
        // Нужно для подсчёта пересечений
        // (это НЕ client_lessons, а lessons)
        $clientLessonsByDate = $this->getLessonsQuery()
            ->with('group')
            ->where('status', LessonStatus::planned)
            ->where('is_unplanned', 0)
            ->get()
            ->groupBy('date');

        $cvpById = ContractVersionProgram::query()
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

        $swampsByProgramId = $this->programs->map(function ($p) use ($cvpById) {
            $cvp = $p['id'] > 0 ? $cvpById[$p['id']] : null;
            $lessonsConducted = $cvp ? $cvp->lessons_conducted : 0;
            $totalLessons = $cvp ? $cvp->total_lessons : 33; // 33 – прогноз по занятиям, чтобы статус был "к исполнению"
            $hasGroup = $p['group_id'] !== null;

            return (object) [
                'id' => $p['id'],
                'program' => $p['program'],
                'contract_id' => $cvp ? $cvp->contractVersion->contract_id : $p['contract_id'],
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

            $group->draft_status = null;

            // есть процесс по договору
            if ($p) {
                $isProgramUsed = true;
                $group->swamp = $swampsByProgramId[$p['id']];

                // процесс по договору есть в черновике, но нет в реальности
                if (@$clientGroups[$p['id']] !== $group->id) {
                    $group->draft_status = 'added';
                }
            } else {
                // процесса по договору нет в черновике, но есть в реальности
                if ($clientGroups->contains($group->id)) {
                    $group->draft_status = 'removed';
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
                // если урок уже начался, но препод его не провёл – считаем кол-во таких
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
                $clientLessonsAtDate = $clientLessonsByDate[$groupLesson->date] ?? collect();

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

        // добавляем вкладку "новый договор"
        $hasNewContract = false;

        // группировка по contractId
        $result = collect();

        foreach ($this->programs as $p) {
            $contractId = $p['contract_id'];
            unset($p['contract_id']); // чтобы значение не дублировалось в $result

            if ($contractId === -1) {
                $hasNewContract = true;
            }
            $swamp = $swampsByProgramId[$p['id']];
            if (! $result->has($contractId)) {
                $result[$contractId] = collect();
            }
            $result[$contractId]->push((object) [
                ...$p, // unset contract_id here – I dont want it in the result
                'swamp' => $swamp,
                'groups' => $groupsByProgram->has($p['program']) ? $groupsByProgram[$p['program']] : [],
            ]);
        }

        // добавляем вкладку "новый договор"
        if (! $hasNewContract) {
            $result[-1] = collect();
        }

        return $result;
    }

    /**
     * Программы из черновика + все остальные.
     * Добавляем contract_id в каждую программу для последующих группировок
     * TODO: всё таки-изменить структуру так, чтобы было contract_id => [..programs]
     * потому что при добавлении новой цепи программ не будет
     */
    // private function allPrograms(): Collection
    // {
    //     return once(fn () => $this->programs
    //         ->merge($this->client->contracts
    //             ->where('year', $this->year)
    //             ->when($this->contract_id, fn ($q) => $q->where('id', '<>', $this->contract_id))
    //             ->flatMap(fn (Contract $contract) => self::getProgramsForContract($contract))
    //         )
    //         ->map(fn ($p) => (object) [
    //             ...$p,
    //             'contract_id' => $this->contract_id,
    //         ]));
    // }

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
    public function addPrograms(array $newPrograms)
    {
        $newPrograms = collect($newPrograms);
        $id = min($this->programs->min('id'), 0);
        $programs = $this->programs;

        // Добавляем новые программы
        foreach ($newPrograms as $program) {
            $id--;
            $programs->push([
                'id' => $id, // id < 0
                'contract_id' => $this->contract_id,
                'program' => $program,
                'group_id' => null,
            ]);
        }

        $this->programs = $programs;
    }

    /**
     * Удалить ранее добавленную программу из проекта
     * ID всегда негативный
     */
    public function removeProgram(int $id)
    {
        $this->programs = $this->programs->filter(fn ($p) => $p['id'] !== $id);
    }

    // public function getProgramsAttribute(): Collection
    // {
    //     return once(fn () => collect($this->data)->map(fn ($p) => (object) $p));
    // }

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

    /**
     * Получить данные для заполнения договора
     *
     * @see ContractVersionResource
     */
    public function fillContract()
    {
        $activeVersion = $this->contract->active_version;
        $item = json_redecode(new ContractVersionResource($activeVersion), false);

        $programs = collect();
        $id = -1;
        $pricesId = -1;
        foreach ($this->programs as $p) {
            $programFromContract = $activeVersion->programs->where('program', $p['program'])->first();
            // группа, в которую ученика поместили в проекте (или он там и был)
            $groupFromDraft = $p['group_id'] ? Group::find($p['group_id']) : null;
            $lessonsConducted = $programFromContract?->lessons_conducted ?: 0;
            $lessonsTotal = $programFromContract?->lessons_total ?: 0;
            $program = [
                'id' => $id,
                'program' => $p['program'],
                'group_id' => $p['group_id'],
                'lessons_planned' => $programFromContract?->lessons_planned ?: 0,
                'lessons_conducted' => $lessonsConducted,
                'lessons_to_be_conducted' => $programFromContract?->lessons_to_be_conducted ?: 0,
                'lessons_total' => $lessonsTotal,
                'client_lesson_prices' => $programFromContract?->client_lesson_prices ?: [],
                'status' => ContractVersionProgram::getStatus(
                    $lessonsConducted,
                    $lessonsTotal,
                    $groupFromDraft !== null
                ),
            ];
            $prices = collect();
            if ($programFromContract) {
                foreach ($programFromContract->prices as $index => $price) {
                    // кол-во занятий может измениться только в последней цене; предыдущие – это то, что было по факту
                    // кол-во занятий меняется только в случае, если ученика переместили в другую группу,
                    // где кол-во занятий отличается от той, где он был
                    $updateLessons = $index === $programFromContract->prices->count() - 1
                        && $groupFromDraft
                        && $groupFromDraft->id !== $programFromContract->clientGroup?->group_id;

                    $lessons = $updateLessons
                        ? ($groupFromDraft->lessons_planned - $lessonsConducted)
                        : $price->lessons;

                    $prices->push([
                        'id' => $pricesId,
                        'contract_version_program_id' => $id,
                        'lessons' => $lessons,
                        'price' => $price->price,
                    ]);

                    $pricesId--;
                }
            }

            if ($p['id'] < 0) {
                $prices->push([
                    'id' => $pricesId,
                    'contract_version_program_id' => $id,
                    'lessons' => $groupFromDraft ? ($groupFromDraft->lessons_planned - $lessonsConducted) : 0,
                    'price' => 0,
                ]);
            }

            $program['prices'] = $prices;
            $programs->push($program);
            $id--;
            $pricesId--;
        }
        $item->programs_original = $item->programs;
        $item->programs = $programs;

        return $item;
    }
}
