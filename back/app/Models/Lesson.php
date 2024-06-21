<?php

namespace App\Models;

use Carbon\Carbon;
use App\Enums\Cabinet;
use App\Enums\LessonStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = [
        'teacher_id',
        'group_id',
        'price',
        'cabinet',
        'start_at',
        'status',
        'topic',
        'conducted_at',
        'is_topic_verified',
        'is_unplanned'
    ];

    protected $casts = [
        'is_topic_verified' => 'boolean',
        'is_unplanned' => 'boolean',
        'status' => LessonStatus::class,
        'cabinet' => Cabinet::class,
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chain()
    {
        return $this->hasMany(Lesson::class, 'group_id', 'group_id');
    }

    public function scopeConducted($query)
    {
        return $query->where('status', LessonStatus::conducted);
    }

    public function date(): Attribute
    {
        return Attribute::make(
            fn () => str($this->start_at)->before(' ')
        );
    }

    public function time(): Attribute
    {
        return Attribute::make(
            fn () => str($this->start_at)->after(' ')->beforeLast(':')
        );
    }

    /**
     * Время конца занятия
     */
    public function timeEnd(): Attribute
    {
        return Attribute::make(
            fn () => Carbon::parse($this->start_at)
                ->addMinutes($this->group->duration)
                ->format("H:i")
        );
    }

    /**
     * Является первым занятием в группе
     */
    public function isFirst(): Attribute
    {
        return Attribute::make(
            fn (): bool => !$this
                ->chain()
                ->where('start_at', '<', $this->start_at)
                ->exists()
        );
    }
}
