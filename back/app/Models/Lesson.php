<?php

namespace App\Models;

use _PHPStan_ac6dae9b0\Nette\Neon\Exception;
use App\Casts\JsonArrayCast;
use App\Enums\Cabinet;
use App\Enums\ClientLessonStatus;
use App\Enums\LessonStatus;
use App\Http\Resources\LessonListResource;
use App\Observers\LessonObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

#[ObservedBy(LessonObserver::class)]
class Lesson extends Model
{
    protected $fillable = [
        'teacher_id', 'group_id', 'price', 'cabinet', 'date', 'time', 'status',
        'topic', 'conducted_at', 'is_topic_verified', 'is_unplanned', 'quarter',
        'homework', 'files', 'is_free', 'is_violation', 'violation_comment',
        'is_substitute',
    ];

    protected $casts = [
        'is_topic_verified' => 'boolean',
        'is_unplanned' => 'boolean',
        'is_free' => 'boolean',
        'is_substitute' => 'boolean',
        'status' => LessonStatus::class,
        'cabinet' => Cabinet::class,
        'files' => JsonArrayCast::class,
    ];

    /**
     * @param  Collection<int, Lesson>  $lessons
     */
    public static function withSequenceNumber(Collection $lessons)
    {
        $result = collect();
        foreach ($lessons->groupBy('group.program') as $programLessons) {
            $seq = 1;
            foreach ($programLessons->sortBy(['date', 'time'])->values()->all() as $lesson) {
                if ($lesson->status !== LessonStatus::cancelled) {
                    $lesson->setAttribute('seq', $seq);
                    $seq++;
                }
                $result->push($lesson);
            }
        }

        return paginate(LessonListResource::collection(
            $result->sortBy(['date', 'time'])->values()->all()
        ));
    }

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

    public function violations(): HasMany
    {
        return $this->hasMany(Violation::class);
    }

    public function scopeConducted($query)
    {
        return $query->where('status', LessonStatus::conducted);
    }

    public function getDateTimeAttribute(): Carbon
    {
        return Carbon::parse(implode(' ', [$this->date, $this->time]));
    }

    public function getTimeFormattedAttribute(): string
    {
        if (! $this->attributes['time']) {
            return '';
        }

        [$h, $m, $s] = explode(':', $this->attributes['time']);

        return "$h:$m";
    }

    /**
     * Время конца занятия
     */
    public function getTimeEndAttribute(): string
    {
        return $this->date_time
            ->addMinutes($this->group->program->getDuration())
            ->format('H:i:s');
    }

    /**
     * Является первым занятием в группе
     */
    public function isFirst(): bool
    {
        return ! $this->chain()
            ->where(DB::raw("concat(`date`, ' ', `time`)"), '<', $this->date_time->format('Y-m-d H:i:s'))
            ->exists();
    }

    public function chain(): HasMany
    {
        return $this->hasMany(Lesson::class, 'group_id', 'group_id');
    }

    /**
     * Проводка занятия
     */
    public function conduct($students)
    {
        DB::transaction(function () use ($students) {
            foreach ($students as $s) {
                $s = (object) $s;

                $contractVersionProgram = ContractVersionProgram::find($s->contract_version_program_id);

                // занятие можно провести только если contractVersionProgram относится к активному договору
                if (! $contractVersionProgram->contractVersion->is_active) {
                    throw new Exception(sprintf(
                        'Попытка провести занятие по программе ID %d, которая относится к неактивной версии договора',
                        $contractVersionProgram->id,
                    ));
                }

                $clientLesson = new ClientLesson([
                    'status' => $s->status,
                    'comment' => $s->comment,
                    'minutes_late' => $s->status === ClientLessonStatus::late->value
                        ? $s->minutes_late
                        : null,
                    'price' => $this->is_free ? 0 : $contractVersionProgram->getNextPrice(),
                    'scores' => $s->scores,
                ]);
                $clientLesson->contract_version_program_id = $contractVersionProgram->id;
                $this->clientLessons()->save($clientLesson);
            }

            $this->update([
                'conducted_at' => now(),
                'status' => LessonStatus::conducted,
            ]);
        });
    }

    public function clientLessons(): HasMany
    {
        return $this->hasMany(ClientLesson::class);
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
            ->selectRaw('client_lessons.*, cv.contract_id')
            ->first();
    }

    /**
     * Занятие нужно провести (прошло более часа с начала)
     */
    public function getIsNeedConductAttribute(): bool
    {
        return $this->status === LessonStatus::planned
            && $this->date_time->addHour()->isPast();
    }

    public function getDateObjAttribute(): Carbon
    {
        return Carbon::parse($this->date);
    }

    public function scopeNeedConduct($query)
    {
        $query
            ->where('status', LessonStatus::planned)
            ->whereRaw('TIMESTAMP(`date`, `time`) < NOW() - INTERVAL 1 HOUR')
            // смотрим начиная со вчерашнего дня
            ->where('date', '<', now()->format('Y-m-d'));
    }
}
