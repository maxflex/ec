<?php

namespace App\Models;

use App\Casts\JsonArrayCast;
use App\Enums\Cabinet;
use App\Enums\ClientLessonStatus;
use App\Enums\LessonStatus;
use App\Http\Resources\LessonListResource;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * @property ?object $clientLesson проведённое занятия в расписании клиента
 */
class Lesson extends Model
{
    protected $fillable = [
        'teacher_id', 'group_id', 'price', 'cabinet', 'date', 'time',
        'status', 'topic', 'conducted_at', 'is_topic_verified', 'is_unplanned',
        'quarter', 'homework', 'files'
    ];

    protected $casts = [
        'is_topic_verified' => 'boolean',
        'is_unplanned' => 'boolean',
        'status' => LessonStatus::class,
        'cabinet' => Cabinet::class,
        'files' => JsonArrayCast::class,
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clientLessons(): HasMany
    {
        return $this->hasMany(ClientLesson::class);
    }

    public function chain(): HasMany
    {
        return $this->hasMany(Lesson::class, 'group_id', 'group_id');
    }

    public function scopeConducted($query)
    {
        return $query->where('status', LessonStatus::conducted);
    }

    public function getDateTimeAttribute()
    {
        return join(' ', [$this->date, $this->time]);
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
                ->active()
                ->first()
                ->programs()
                ->where('program', $this->group->program)
                ->first();

            if ($contractVersionProgram === null) {
                throw new Exception('No contract version program found');
            }

            $this->clientLessons()->create([
                'contract_id' => $c->id,
                'status' => $c->status,
                'minutes_late' => $c->status === ClientLessonStatus::late->value
                    ? $c->minutes_late
                    : null,
                'is_remote' => $c->is_remote,
                'price' => $contractVersionProgram->getNextPrice(),
                'scores' => count($c->scores) ? $c->scores : null
            ]);
        }

        $this->update([
            'conducted_at' => now(),
            'status' => LessonStatus::conducted
        ]);
    }

    /**
     * @param Collection<int, Lesson> $lessons
     */
    public static function withSequenceNumber(Collection $lessons)
    {
        $seq = 1;
        $lessons = $lessons->sortBy(['date', 'time'])->values();
        foreach ($lessons->all() as $lesson) {
            if ($lesson->status === LessonStatus::cancelled) {
                continue;
            }
            $lesson->setAttribute('seq', $seq);
            $seq++;
        }
        return paginate(LessonListResource::collection($lessons));
    }
}
