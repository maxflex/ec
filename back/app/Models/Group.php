<?php

namespace App\Models;

use App\Enums\Program;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Group extends Model
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

    public function contracts()
    {
        return $this->belongsToMany(Contract::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function getSchedule(): Collection
    {
        $schedule = Lesson::query()
            ->where('group_id', $this->id)
            ->get();

        return $schedule->unique(fn ($l) => $l->id);
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
            'contracts',
            fn ($q) => $q->where('client_id', $clientId)
        );
    }

    /**
     * Нажимаем "добавить ученика в текущую группу"
     * Получаем список кандидатов
     *
     * @return ContractVersionProgram[]
     */
    public function getCandidates()
    {
        return ContractVersionProgram::query()
            ->where('program', $this->program)
            ->whereHas(
                'contractVersion',
                fn ($q) => $q
                    ->lastVersions()
                    ->whereHas(
                        'contract',
                        fn ($q) => $q->where('year', $this->year)->whereDoesntHave('groups')
                    )
            )
            ->get();
    }
}
