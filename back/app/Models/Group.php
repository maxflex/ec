<?php

namespace App\Models;

use App\Contracts\HasSchedule;
use App\Enums\Cabinet;
use App\Enums\LessonStatus;
use App\Enums\Program;
use App\Observers\UserIdObserver;
use App\Traits\HasScheduleTrait;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

#[ObservedBy(UserIdObserver::class)]
class Group extends Model implements HasSchedule
{
    use HasScheduleTrait;

    protected $fillable = [
        'program', 'year', 'zoom', 'lessons_planned',
        'contract_date', 'letter', 'is_in_contract',
    ];

    protected $casts = [
        'zoom' => 'array',
        'schedule' => 'array',
        'program' => Program::class,
        'is_in_contract' => 'bool',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function acts(): HasMany
    {
        return $this->hasMany(GroupAct::class);
    }

    public function clientGroups(): HasMany
    {
        return $this->hasMany(ClientGroup::class);
    }

    public function getZoomAttribute($value)
    {
        return $value === null ? [
            'id' => null,
            'password' => null,
        ] : json_decode($value);
    }

    public function scopeWhereTeacher($query, $teacherId)
    {
        $query->whereHas(
            'lessons',
            fn ($q) => $q->where('teacher_id', $teacherId)
        );
    }

    public function scopeWhereClient($query, $clientId)
    {
        $query->whereHas(
            'clientGroups.contractVersionProgram.contractVersion.contract',
            fn ($q) => $q->where('client_id', $clientId)
        );
    }

    /**
     * Кол-во учеников в проектах договора
     */
    public function getProjectStudentsCountAttribute(): int
    {
        $result = 0;

        // факт
        $cvpIdsReal = $this->clientGroups->pluck('contract_version_program_id');

        // проект
        foreach (Project::getAllActive() as $project) {
            foreach ($project->programs as $p) {
                // реально есть в группе
                if ($cvpIdsReal->contains($p['id'])) {
                    // но в проекте убрали
                    if ($p['group_id'] !== $this->id) {
                        $result--;
                    }
                } else {
                    // реально нет в группе
                    if ($p['group_id'] === $this->id) {
                        // но в проекте добавили
                        $result++;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Проектные ученики
     */
    public function getProjectStudentsAttribute(): Collection
    {
        $clients = collect();

        // факт
        $cvpIdsReal = $this->clientGroups->pluck('contract_version_program_id');

        // проект
        foreach (Project::getAllActive() as $project) {
            foreach ($project->programs as $p) {
                $cvpId = $p['id'];

                // реально есть в группе
                if ($cvpIdsReal->contains($cvpId)) {
                    // но в проекте убрали
                    if ($p['group_id'] !== $this->id) {
                        $clients->push((object) [
                            'id' => uniqid(),
                            'contract_version_program_id' => $cvpId,
                            'client' => $project->client,
                            'group_id' => $p['group_id'],
                            'project_id' => $project->id,
                            'is_removed' => true, // удален
                        ]);
                    }
                } else {
                    // реально нет в группе
                    if ($p['group_id'] === $this->id) {
                        // но в проекте добавили
                        $clients->push((object) [
                            'id' => uniqid(),
                            'contract_version_program_id' => $cvpId,
                            'client' => $project->client,
                            'group_id' => $p['group_id'],
                            // реальный непроектный group_id
                            'real_group_id' => ClientGroup::where('contract_version_program_id', $cvpId)->first()?->group_id,
                            'project_id' => $project->id,
                            'is_removed' => false, // добавлен
                        ]);
                    }
                }
            }
        }

        return $clients;
    }

    /**
     * Нажимаем "добавить ученика в текущую группу"
     * Получаем список кандидатов
     */
    public function getCandidates()
    {
        return ContractVersionProgram::query()
            ->where('program', $this->program)
            ->whereHas(
                'contractVersion',
                fn ($q) => $q
                    ->active()
                    ->whereHas(
                        'contract',
                        fn ($q) => $q->where('year', $this->year)->whereDoesntHave('groups')
                    )
            )
            ->get();
    }

    /**
     * Минимальная вместимость среди всех кабинетов
     */
    public function getCapacityAttribute(): int
    {
        $cabinets = $this->cabinets;

        if ($cabinets->isEmpty()) {
            return 0;
        }

        return $cabinets->min(fn (Cabinet $c) => $c->capacity());
    }

    /**
     * Кабинеты из планируемых занятий
     *
     * @return Collection<int, Cabinet>
     */
    public function getCabinetsAttribute(): Collection
    {
        return $this->lessons->pluck('cabinet')->unique();
    }

    public function getTeacherAttribute(): ?Teacher
    {
        return $this->teachers[0];
    }

    /**
     * @return Teacher[]
     */
    public function getTeachersAttribute(): array
    {
        return once(fn () => Teacher::query()
            ->whereIn('id', $this->lessons
                ->where('status', '<>', LessonStatus::cancelled)
                ->pluck('teacher_id')
                ->unique()
            )
            ->get()
            ->all()
        );
    }

    /**
     * Препод: кол-во занятий у этого препода в этой группе
     */
    public function getTeacherCountsAttribute(): object
    {
        $result = [];
        foreach ($this->teachers as $teacher) {
            $result[$teacher->id] = $this->lessons
                ->where('teacher_id', $teacher->id)
                ->where('status', '<>', LessonStatus::cancelled)
                ->count();
        }

        return (object) $result;
    }

    /**
     * @return array{conducted: number, conducted_free: number, planned: number, planned_free: number}
     */
    public function getLessonCountsAttribute(): array
    {
        $nonCancelledLessons = $this->lessons->where('status', '<>', LessonStatus::cancelled);
        $conductedLessons = $this->lessons->where('status', LessonStatus::conducted);

        return [
            'conducted' => $conductedLessons->where('is_free', false)->count(),
            'conducted_free' => $conductedLessons->where('is_free', true)->count(),
            'planned' => $nonCancelledLessons->where('is_free', false)->count(),
            'planned_free' => $nonCancelledLessons->where('is_free', true)->count(),
        ];
    }

    public function getFirstLessonDateAttribute(): ?string
    {
        return $this->lessons->min('date');
    }

    /**
     * Получить "зубы" группы
     */
    public function getScheduleQuery(int $year): object
    {
        return $this->lessons()->getQuery();
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }
}
