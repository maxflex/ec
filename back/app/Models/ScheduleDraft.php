<?php

namespace App\Models;

use App\Contracts\HasTeeth;
use App\Enums\LessonStatus;
use App\Http\Resources\ContractVersionResource;
use App\Http\Resources\PersonResource;
use App\Utils\Teeth;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class ScheduleDraft extends Model implements HasTeeth
{
    protected $fillable = [
        'programs', 'client_id', 'user_id',
        'year',
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
        $scheduleDraft = new ScheduleDraft([
            'client_id' => $client->id,
            'year' => $year,
        ]);

        $scheduleDraft->programs = $scheduleDraft->getActualPrograms($year);

        return $scheduleDraft;
    }

    private function getActualPrograms(int $year): Collection
    {
        $contracts = $this->client->contracts()->where('year', $year)->get();

        $programs = collect();
        foreach ($contracts as $contract) {
            $programs = $programs->merge(self::getPrograms($contract));
        }

        return $programs;
    }

    /**
     * Получить programs для договора
     */
    private static function getPrograms(Contract $contract)
    {
        return $contract->active_version->programs->map(
            fn (ContractVersionProgram $cvp) => [
                'id' => $cvp->id,
                'contract_id' => $contract->id,
                'program' => $cvp->program->value,
                'group_id' => $cvp->clientGroup?->group_id,
            ]);
    }

    /**
     * Для селектора в диалоге создания версии
     * Доступные для загрузки проекты
     */
    // public static function selectorForContract(Contract $contract)
    // {
    //     $scheduleDrafts = $contract->client->scheduleDrafts()
    //         ->where('created_at', '>', $contract->active_version->created_at->format('Y-m-d H:i:s'))
    //         ->where('year', $contract->year)
    //         ->get()
    //         ->sortByDesc('created_at')
    //         ->values();
    //
    //     $scheduleDrafts = json_redecode(SavedScheduleDraftResource::collection($scheduleDrafts));
    //
    //     $result = [];
    //     foreach ($scheduleDrafts as $scheduleDraft) {
    //         $changes = $scheduleDraft['changes'];
    //         if (isset($changes[$contract->id])) {
    //             $result[] = $scheduleDraft;
    //         }
    //     }
    //
    //     return $result;
    // }

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
     * Применить перемещения в группе
     */
    public function applyMoveGroups()
    {
        $programs = $this->programs;

        // если есть добавления в группу по новым программам – ошибка
        if ($programs->where('id', '<', 0)->whereNotNull('group_id')->count()) {
            throw new Exception('Нельзя добавить в группу по несуществующей программе');
        }

        // only real programs
        $programs = $programs->where('id', '>', 0);

        $clientGroups = ClientGroup::query()
            ->whereIn('contract_version_program_id', $programs->pluck('id'))
            ->get();

        foreach ($programs as $p) {
            $programId = $p['id'];
            $groupId = $p['group_id'];
            $clientGroup = $clientGroups->where('contract_version_program_id', $programId)->first();

            if ($clientGroup) {
                if (! $groupId) {
                    // Было, но удалили
                    $clientGroup->delete();
                } elseif ($clientGroup->group_id !== $groupId) {
                    // Было, но изменили
                    $clientGroup->group_id = $groupId;
                    $clientGroup->save();
                }
            } elseif ($groupId) {
                // Не было, но добавили
                ClientGroup::create([
                    'contract_version_program_id' => $programId,
                    'group_id' => $groupId,
                ]);
            }
        }
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
    //             ->flatMap(fn (Contract $contract) => self::getPrograms($contract))
    //         )
    //         ->map(fn ($p) => (object) [
    //             ...$p,
    //             'contract_id' => $this->contract_id,
    //         ]));
    // }
    /**
     *  Применить перемещения в группе (при сохранении договора)
     */
    public function applyMoveGroupsContract(ContractVersion $cv)
    {
        // сначала всё сносим
        foreach ($cv->programs as $program) {
            $program->clientGroup?->delete();
        }

        // и добавляем согласно черновику
        // программы выбранного $contractId
        $programs = $this->programs
            ->filter(fn ($p) => $p['group_id'] && $p['contract_id'] === $cv->contract_id);

        foreach ($programs as $p) {
            $programFromContract = $cv->programs->firstWhere('program', $p['program']);
            if ($programFromContract) {
                ClientGroup::create([
                    'group_id' => $p['group_id'],
                    'contract_version_program_id' => $programFromContract->id,
                ]);
            }
        }
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
            ->with('contractVersionProgram.contractVersion')
            ->get()
            ->keyBy('group_id');

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

            $group->original_contract_id = $clientGroups->has($group->id)
                ? $clientGroups[$group->id]->contractVersionProgram->contractVersion->contract_id
                : null;

            $group->current_contract_id = null;

            // есть процесс по договору
            if ($p) {
                $isProgramUsed = true;
                $swamp = $swampsByProgramId[$p['id']];
                if ($swamp) {
                    $group->swamp = $swamp;
                    $group->current_contract_id = $swamp->contract_id;
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
                if (now()->format('Y-m-d H:i:s') >= $groupLesson->date_time && $groupLesson->status === LessonStatus::planned) {
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
                'overlap', 'uncunducted_count', 'original_contract_id',
                'current_contract_id',
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

    private function getLessonsQuery()
    {
        $groupIds = $this->programs->whereNotNull('group_id')->pluck('group_id')->unique();

        return Lesson::query()->whereIn('group_id', $groupIds);
    }

    public function getTeeth(?int $year = null): object
    {
        return Teeth::get($this->getLessonsQuery());
    }

    // public function getProgramsAttribute(): Collection
    // {
    //     return once(fn () => collect($this->data)->map(fn ($p) => (object) $p));
    // }

    /**
     * Добавить новые программы в проект договора
     * Добавление всегда в текущий contract_id
     */
    public function addPrograms(array $newPrograms, int $contractId)
    {
        $newPrograms = collect($newPrograms);
        $id = min($this->programs->min('id'), 0);
        $programs = $this->programs;

        // Добавляем новые программы
        foreach ($newPrograms as $program) {
            $id--;
            $programs->push([
                'id' => $id, // id < 0
                'contract_id' => $contractId,
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Программы из проекта + реальные
     */
    public function getAllProgramsAttribute()
    {
        $otherContracts = $this->client->contracts()
            ->where('year', $this->year)
            ->when($this->contract_id, fn ($q) => $q->where('id', '<>', $this->contract_id))
            ->get();

        $otherPrograms = collect();

        foreach ($otherContracts as $contract) {
            $otherPrograms = $otherPrograms->merge(
                self::getPrograms($contract)
            );
        }

        return $this->programs->merge($otherPrograms);
    }

    /**
     * Получить данные для заполнения договора
     */
    public function fillContract()
    {
        return $this->contract_id
           ? $this->newContractVersion()
           : $this->newContract();
    }

    /**
     * @see ContractVersionResource
     */
    private function newContractVersion()
    {
        $activeVersion = $this->contract->active_version;
        $item = json_redecode(new ContractVersionResource($activeVersion), false);

        $programs = collect();
        $id = -1;
        $pricesId = -1;
        foreach ($this->programs as $p) {
            if ($p['contract_id'] !== $this->contract_id) {
                continue;
            }
            $programFromContract = $activeVersion->programs->where('program', $p['program'])->first();
            // группа, в которую ученика поместили в проекте (или он там и был)
            $groupFromDraft = $p['group_id'] ? Group::find($p['group_id']) : null;
            $lessonsConducted = $programFromContract?->lessons_conducted ?: 0;
            $lessonsSuggest = $programFromContract ? $programFromContract->getLessonsSuggest($groupFromDraft) : 0;

            $program = [
                'id' => $id,
                'program' => $p['program'],
                'group_id' => $p['group_id'],
                'lessons_planned' => $programFromContract?->lessons_planned ?: 0,
                'lessons_conducted' => $lessonsConducted,
                'lessons_to_be_conducted' => $programFromContract?->lessons_to_be_conducted ?: 0,
                'lessons_suggest' => $lessonsSuggest,
                'client_lesson_prices' => $programFromContract?->client_lesson_prices ?: [],
                'status' => ContractVersionProgram::getStatus(
                    $lessonsConducted,
                    $programFromContract?->total_lessons ?: 0,
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
                        // сколько планируется в группе минус сколько проведено по программе
                        ? ($groupFromDraft->lesson_counts['planned'] - $lessonsConducted)
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

        // set ID=-1 for payments
        $payments = [];
        foreach ($item->payments as $payment) {
            $payments[] = [
                ...(array) $payment,
                'id' => $id,
            ];
            $id--;
        }

        $item->programs_original = $item->programs;
        $item->programs = $programs;
        $item->payments = $payments;

        return $item;
    }

    /**
     * @see modelDefaults: ContractVersionResource
     */
    private function newContract()
    {
        $item = (object) [
            'id' => -1,
            'seq' => 1,
            'date' => now()->format('Y-m-d'),
            'programs' => collect(),
            'programs_original' => collect(),
            'payments' => collect(),
            'contract' => [
                'year' => $this->year,
                'client_id' => $this->client_id,
                'source' => null,
                'company' => null,
            ],
        ];

        $programs = $this->getProgramsOnlyForSelectedContract(-1);

        $id = -1;
        $pricesId = -1;
        foreach ($programs as $p) {
            $groupFromDraft = $p['group_id'] ? Group::find($p['group_id']) : null;

            $item->programs->push([
                'id' => $id,
                'program' => $p['program'],
                'group_id' => $p['group_id'],
                'lessons_planned' => 0,
                'lessons_conducted' => 0,
                'lessons_to_be_conducted' => 0,
                'lessons_suggest' => 0,
                'client_lesson_prices' => [],
                'status' => ContractVersionProgram::getStatus(
                    0,
                    0,
                    $groupFromDraft !== null
                ),
                'prices' => [(object) [
                    'id' => $pricesId,
                    'price' => null,
                    'lessons' => $groupFromDraft?->lessons_planned,
                ]],
            ]);

            $id--;
            $pricesId--;
        }

        return $item;
    }

    public function getProgramsOnlyForSelectedContract(int $contractId)
    {
        return $this->programs->filter(
            fn ($p) => $p['contract_id'] === $contractId
        )->values();
    }

    /**
     * Проект по договору более не актуален (после него уже была создана версия)
     */
    public function getIsArchivedAttribute(): bool
    {
        $createdAt = $this->contract_id
            ? $this->contract->active_version->created_at
            : $this->client->contracts()->where('year', $this->year)
                ->get()
                ->map(fn (Contract $c) => $c->versions[0]->created_at)
                ->max();

        return $createdAt > $this->created_at;
    }

    /**
     * Изменения в проекте
     */
    public function getChangesAttribute(): int
    {
        if ($this->is_archived) {
            return 0;
        }

        $programs = $this->programs;
        $originalPrograms = $this->contract_id ? self::getPrograms($this->contract) : collect();

        $changes = 0;

        foreach ($programs as $p) {
            $originalProgram = $originalPrograms->firstWhere('id', $p['id']);

            // новая программа
            if (! $originalProgram) {
                $changes++;
            }

            // original:    program_id = 1 | group_id = 5

            // step 1:      program_id = 1 | group_id = null    // убрали и группы
            // step 2:      program_id = 1 | group_id = 6       // добавили в новую группу

            // изменения в группах
            if (@$originalProgram['group_id'] !== $p['group_id']) {
                // if ($this->id === 5) {
                //     logger('', [
                //         @$originalProgram['group_id'],
                //         $p['group_id'],
                //     ]);
                // }
                //

                // отдельно считаем удаление
                if (@$originalProgram['group_id']) {
                    $changes++;
                }

                // отдельно считаем добавление
                if ($p['group_id']) {
                    $changes++;
                }
            }
        }

        return $changes;
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function saveDraft(int $contractId): ScheduleDraft
    {
        $scheduleDraft = $this->replicate();

        $scheduleDraft->contract_id = $contractId < 0 ? null : $contractId;

        $scheduleDraft->programs = $scheduleDraft->getProgramsOnlyForSelectedContract($contractId);

        $scheduleDraft->save();

        return $scheduleDraft;
    }

    // public function getYearAttribute()
    // {
    //     return $this->year ?? ($this->contract_id ? $this->contract->year : current_academic_year());
    // }

    /**
     * Программы из сохранённого проекта + остальные
     * + установка года
     */
    public function unpack()
    {
        $otherContracts = $this->client->contracts()
            ->where('year', $this->year)
            ->when($this->contract_id, fn ($q) => $q->where('id', '<>', $this->contract_id))
            ->get();

        $otherPrograms = collect();

        foreach ($otherContracts as $contract) {
            $otherPrograms = $otherPrograms->merge(
                self::getPrograms($contract)
            );
        }

        $this->programs = $this->programs->merge($otherPrograms);
    }

    /**
     * Загрузить программы из ScheduleDraft в проект из Ram
     * (меню загрузить проект из Editor)
     */
    public function insertPrograms(ScheduleDraft $scheduleDraft)
    {
        $contractId = $scheduleDraft->contract_id ?? -1;

        $programs = collect();

        foreach ($this->programs as $p) {
            if ($p['contract_id'] !== $contractId) {
                $programs->push($p);
            }
        }

        foreach ($scheduleDraft->programs as $p) {
            $programs->push($p);
        }

        $this->programs = $programs;
    }
}
