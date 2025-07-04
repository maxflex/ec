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
        $year = $this->year;

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
            ->with('contractVersion.contract')
            ->whereIn('id', $realProgramIds)
            ->get()
            ->keyBy('id');

        $groups = Group::query()
            ->where('year', $year)
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

        $result = [];
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

        foreach ($this->programs as $p) {
            $item = $p;
            $swamp = $swamps[$p->id];
            $item->swamp = $swamp;
            $item->contract = $swamp->contract;
            $item->groups = $groupsByProgram->has($p->program) ? $groupsByProgram[$p->program] : [];
            $result[] = $item;
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

    /**
     * Добавить (или удалить) новые программы в проект договора
     *
     * - удалить старые с id < 0, если их нет в $newPrograms;
     * - добавить новые с id < 0, если их нет в $newPrograms;
     * - оставить всё остальное как есть.
     */
    public function newPrograms(array $newPrograms)
    {
        $newPrograms = collect($newPrograms);

        $id = min($this->data->min('id'), 0);

        // удаление программ
        $data = $this->data
            // логика: оставляем только реальные (ID > 0) или те, что есть в актуальном $newPrograms
            ->filter(fn ($e) => $e['id'] > 0 || $newPrograms->contains($e['program']))
            ->values();

        // Добавляем недостающие новые программы (id < 0)
        foreach ($newPrograms as $program) {
            if (! $data->contains(fn ($e) => $e['id'] < 0 && $e['program'] === $program)) {
                $id--;
                $data->push([
                    'id' => $id,
                    'program' => $program,
                    'group_id' => null,
                ]);
            }
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
}
