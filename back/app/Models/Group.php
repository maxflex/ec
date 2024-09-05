<?php

namespace App\Models;

use App\Contracts\HasTeeth;
use App\Enums\Program;
use App\Utils\Teeth;
use Illuminate\Database\Eloquent\Model;

class Group extends Model implements HasTeeth
{
    protected $fillable = [
        'duration', 'program', 'year', 'zoom',
    ];

    protected $casts = [
        'program' => Program::class,
        'zoom' => 'array',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clientGroups()
    {
        return $this->hasMany(ClientGroup::class);
    }

    public function lessons()
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

    public function getTeachers()
    {
        return $this->lessons()
            // ->where('status', LessonStatus::planned)
            ->where('is_unplanned', 0)
            ->groupBy('teacher_id')
            ->pluck('teacher_id');
    }
}
