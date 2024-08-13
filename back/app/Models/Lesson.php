<?php

namespace App\Models;

use App\Enums\Cabinet;
use App\Enums\ContractLessonStatus;
use App\Enums\LessonStatus;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Lesson extends Model
{
    protected $fillable = [
        'teacher_id',
        'group_id',
        'price',
        'cabinet',
        'date',
        'time',
        'status',
        'topic',
        'conducted_at',
        'is_topic_verified',
        'is_unplanned',
        'quarter'
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

    public function contractLessons()
    {
        return $this->hasMany(ContractLesson::class);
    }

    public function chain()
    {
        return $this->hasMany(Lesson::class, 'group_id', 'group_id');
    }

    public function scopeConducted($query)
    {
        return $query->where('status', LessonStatus::conducted);
    }

    public function dateTime(): Attribute
    {
        return Attribute::make(
            fn () => join(' ', [$this->date, $this->time])
        );
    }

    /**
     * Время конца занятия
     */
    public function timeEnd(): Attribute
    {
        return Attribute::make(
            fn () => Carbon::parse($this->time)
                ->addMinutes($this->group->duration)
                ->format("H:i:s")
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
                ->where(DB::raw("concat(`date`, ' ', `time`)"), '<', $this->dateTime)
                ->exists()
        );
    }

    /**
     * Проводка занятия
     */
    public function conduct($contracts)
    {
        foreach ($contracts as $c) {
            $c = (object) $c;

            // подразумеваем, что в договоре есть нужная программа
            $contractVersionProgram = ContractVersion::query()
                ->where('contract_id', $c->id)
                ->lastVersions()
                ->first()
                ->programs()
                ->where('program', $this->group->program)
                ->first();

            if ($contractVersionProgram === null) {
                throw new Exception('No contract version program found');
            }

            $this->contractLessons()->create([
                'contract_id' => $c->id,
                'status' => $c->status,
                'minutes_late' => $c->status === ContractLessonStatus::late->value
                    ? $c->minutes_late
                    : null,
                'is_remote' => $c->is_remote,
                'price' => $contractVersionProgram->getNextPrice(),
                'scores' => count($c->scores) ? $c->scores : null
            ]);
        }

        $this->update([
            'status' => LessonStatus::conducted
        ]);
    }
}
