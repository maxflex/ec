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

        return $schedule
            ->unique(fn ($l) => $l->id)
            ->transform(fn ($l) => extract_fields($l, [
                'date', 'time', 'time_end', 'status',
                'cabinet', 'is_first', 'is_unplanned'
            ], [
                'teacher' => extract_fields($l->teacher, [
                    'first_name', 'last_name', 'middle_name'
                ]),
                'group' => extract_fields($l->group, [
                    'program',
                ], [
                    'contracts_count' => $l->group->contracts()->count()
                ])
            ]))
            ->groupBy('date');
    }

    public function scopeWhereTeacher($query, $teacherId)
    {
        return $query->whereHas(
            'lessons',
            fn ($q) => $q->where('teacher_id', $teacherId)
        );
    }
}
