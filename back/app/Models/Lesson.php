<?php

namespace App\Models;

use App\Casts\JsonArrayCast;
use App\Enums\{Cabinet, ClientLessonStatus, LessonStatus};
use App\Http\Resources\LessonListResource;
use Carbon\Carbon;
use Illuminate\Database\{Eloquent\Casts\Attribute,
    Eloquent\Model,
    Eloquent\Relations\BelongsTo,
    Eloquent\Relations\HasMany};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Lesson extends Model
{
    protected $fillable = [
        'teacher_id', 'group_id', 'price', 'cabinet', 'date', 'time',
        'status', 'topic', 'conducted_at', 'is_topic_verified', 'is_unplanned',
        'quarter', 'homework', 'files', 'is_free'
    ];

    protected $casts = [
        'is_topic_verified' => 'boolean',
        'is_unplanned' => 'boolean',
        'is_free' => 'boolean',
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
    public function conduct($students)
    {
        foreach ($students as $s) {
            $s = (object)$s;

            $contractVersionProgram = ContractVersionProgram::find($s->contract_version_program_id);

            $this->clientLessons()->create([
                'contract_version_program_id' => $contractVersionProgram->id,
                'status' => $s->status,
                'minutes_late' => $s->status === ClientLessonStatus::late->value
                    ? $s->minutes_late
                    : null,
                'price' => $this->is_free ? 0 : $contractVersionProgram->getNextPrice(),
                'scores' => count($s->scores) ? $s->scores : null
            ]);
        }

        $this->update([
            'conducted_at' => now(),
            'status' => LessonStatus::conducted
        ]);
    }

    /**
     * Получить clientLesson для выбранного клиента
     */
    public function getClientLesson(int $clientId): ?ClientLesson
    {
        return $this->clientLessons()
            ->join(
                'contract_version_programs as cvp',
                'cvp.id',
                '=',
                'client_lessons.contract_version_program_id'
            )
            ->join('contract_versions as cv', 'cv.id', '=', 'cvp.contract_version_id')
            ->join('contracts as c', 'c.id', '=', 'cv.contract_id')
            ->where('c.client_id', $clientId)
            ->selectRaw('client_lessons.*')
            ->first();
    }

    /**
     * Занятие нужно провести (прошло более часа с начала)
     */
    public function getIsNeedConductAttribute(): bool
    {
        return $this->status === LessonStatus::planned
            && Carbon::parse($this->date_time)->addHour()->isPast();
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

    public function scopeNeedConduct($query)
    {
        $query->where('status', LessonStatus::planned)->whereRaw("
            TIMESTAMP(`date`, `time`) < NOW() - INTERVAL 1 HOUR
        ");
    }
}
