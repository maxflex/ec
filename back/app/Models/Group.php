<?php

namespace App\Models;

use App\Enums\Program;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = [
        'duration', 'program', 'year', 'zoom', 'is_archived'
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

    public function scopeWhereTeacher($query, $teacherId)
    {
        return $query->whereHas(
            'lessons',
            fn ($q) => $q->where('teacher_id', $teacherId)
        );
    }
}
