<?php

namespace App\Models;

use App\Enums\Program;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Group extends Model
{
    protected $fillable = [
        'duration', 'program', 'year', 'zoom', 'is_archived',
        'exam_date'
    ];

    protected $casts = [
        'program' => Program::class,
        'zoom' => 'array',
        'is_archived' => 'boolean',
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
        return $this->hasMany(Lesson::class)->orderBy('start_at');
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
}
