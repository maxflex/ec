<?php

namespace App\Models;

use App\Contracts\HasTeeth;
use App\Enums\Direction;
use App\Enums\LessonStatus;
use App\Enums\Program;
use App\Utils\Teeth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model implements HasTeeth
{
    protected $fillable = [
        'program', 'year', 'zoom',
    ];

    protected $casts = [
        'program' => Program::class,
        'zoom' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clientGroups(): HasMany
    {
        return $this->hasMany(ClientGroup::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }


    public function getZoomAttribute($value)
    {
        return json_decode($value) ?? [
            'id' => '',
            'password' => ''
        ];
    }

    public function scopeWhereTeacher($query, $teacherId)
    {
        return $query->whereHas(
            'lessons',
            fn ($q) => $q->where('teacher_id', $teacherId)
        );
    }

    public function scopeWhereClient($query, $clientId)
    {
        return $query->whereHas(
            'clientGroups.contractVersionProgram.contractVersion.contract',
            fn($q) => $q->where('client_id', $clientId)
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
        return Teeth::get($this->lessons()->getQuery(), $year);
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
        $ids = $this->lessons()
            ->where('status', LessonStatus::planned)
            ->where('is_unplanned', 0)
            ->groupBy('teacher_id')
            ->pluck('teacher_id');

        // если запланированных занятий нет, берём из последнего
        // https://doc.ege-centr.ru/doc/49
        if ($ids->count() === 0) {
            $ids = [
                $this->lessons()->latest('date')->value('teacher_id')
            ];
        }

        return Teacher::whereIn('id', $ids)->get()->all();
    }

    /**
     * Всего 2 варианта - 125 и 55 минут (8-9 классы школа)
     */
    public function getDurationAttribute(): int
    {
        $direction = Direction::fromProgram($this->program);

        if (in_array($direction, [
            Direction::school8,
            Direction::school9,
        ])) {
            return 55;
        }

        return 125;
    }
}
