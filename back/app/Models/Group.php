<?php

namespace App\Models;

use App\Contracts\HasTeeth;
use App\Enums\LessonStatus;
use App\Enums\Program;
use App\Utils\Teeth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model implements HasTeeth
{
    protected $fillable = [
        'program', 'year', 'zoom', 'lessons_planned',
    ];

    protected $casts = [
        'zoom' => 'array',
        'program' => Program::class,
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
     * Получить "зубы" группы
     */
    public function getTeeth(int $year): object
    {
        return Teeth::get($this->lessons()->getQuery(), $year, true);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
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

    public function getLessonCountsAttribute(): array
    {
        $nonCancelledLessons = $this->lessons->filter(fn (Lesson $l) => $l->status !== LessonStatus::cancelled);
        $conductedLessons = $this->lessons->filter(fn (Lesson $l) => $l->status === LessonStatus::conducted);

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
}
