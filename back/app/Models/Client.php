<?php

namespace App\Models;

use App\Contracts\HasSchedule;
use App\Enums\CvpStatus;
use App\Enums\Direction;
use App\Enums\HeadAboutUs;
use App\Enums\LessonStatus;
use App\Traits\HasComments;
use App\Traits\HasScheduleTrait;
use App\Traits\IsSearchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

class Client extends Person implements HasSchedule
{
    use HasComments, HasScheduleTrait, IsSearchable;

    protected $fillable = [
        'first_name', 'last_name', 'middle_name', 'branches',
        'head_teacher_id', 'passport', 'is_remote', 'email',
        'heard_about_us', 'mark_sheet',
    ];

    protected $casts = [
        'passport' => 'array',
        'schedule' => 'array',
        'is_remote' => 'bool',
        'mark_sheet' => 'array',
        'heard_about_us' => HeadAboutUs::class,
    ];

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function tests(): HasMany
    {
        return $this->hasMany(ClientTest::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(ClientPayment::class, 'entity');
    }

    public function parent(): HasOne
    {
        return $this->hasOne(ClientParent::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branches(): Attribute
    {
        return Attribute::make(
            fn ($value) => $value ? explode(',', $value) : [],
            fn ($value) => $value ? implode(',', $value) : null
        );
    }

    public function headTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'head_teacher_id');
    }

    /**
     * @return array<int, Request>
     */
    public function getRequestsAttribute(): array
    {
        $allNumbers = $this->getPhoneNumbers()->merge($this->parent->getPhoneNumbers());

        return Request::whereHas('phones', fn ($q) => $q->whereIn('number', $allNumbers))->get()->all();
    }

    /**
     * Фактически проведенные занятия.
     * Занятия прошедшие без ученика в группе, в которой ученик сейчас присутствует.
     * Отмененные занятия в группе, в которой ученик сейчас присутствует.
     * Планируемые занятия в группе, в которой ученик сейчас присутствует
     */
    public function getJournal(int $year): Collection
    {
        $schedule = collect();

        // минимальная дата проведённого занятия
        // key = group_id, value = date
        $minConductedDate = [];

        // максимальная дата проведённого занятия
        // key = group_id, value = date
        $maxConductedDate = [];

        // проведённые занятия
        $clientLessons = ClientLesson::query()
            ->whereHas(
                'contractVersionProgram.contractVersion.contract',
                fn ($q) => $q->where('client_id', $this->id)->where('year', $year)
            )
            ->with('lesson')
            ->get();

        foreach ($clientLessons as $clientLesson) {
            $lesson = $clientLesson->lesson;
            // $lesson->load('clientLessons', fn ($q) => $q->whereId($clientLesson->id));
            $lesson->setAttribute('client_lesson', $clientLesson);
            $schedule->push($lesson);
            if (! isset($minConductedDate[$lesson->group_id]) || $lesson->date < $minConductedDate[$lesson->group_id]) {
                $minConductedDate[$lesson->group_id] = $lesson->date;
            }
            if (! isset($maxConductedDate[$lesson->group_id]) || $lesson->date > $maxConductedDate[$lesson->group_id]) {
                $maxConductedDate[$lesson->group_id] = $lesson->date;
            }
        }

        // планируемые занятия (только если ученик в группе)
        $clientGroups = ClientGroup::query()
            ->whereHas(
                'contractVersionProgram.contractVersion.contract',
                fn ($q) => $q->where('client_id', $this->id)->where('year', $year)
            )
            ->with('group.lessons',
                fn ($q) => $q->with('teacher')->where('status', LessonStatus::planned)
            )
            ->get();

        $inGroup = []; // ID групп, где сейчас ученик
        foreach ($clientGroups as $clientGroup) {
            $group = $clientGroup->group;
            $inGroup[$group->id] = true;

            // Пушим все запланированные занятия группы в расписание
            $schedule->push(...$group->lessons);
        }

        // Костя, [17 Jan 2025 at 15:31:17]:
        // если проведенных занятий нет (ученик-группа) то отображаются отмены только будущие
        // если проведенные занятия есть и человек в группе то отображаются отмены этой группы начиная с 1 проведенного занятия в группе
        // если проведенные занятия есть и человек НЕ в группе то отображаются отмены этой группы четко между первых и последним посещением этой группы у этого человека
        $cancelledLessons = Lesson::query()
            ->where('status', LessonStatus::cancelled)
            ->whereIn('group_id', $schedule->pluck('group_id')->unique()->values())
            ->get();

        foreach ($cancelledLessons as $lesson) {
            // есть проведённые занятия
            if (isset($minConductedDate[$lesson->group_id])) {
                // человек в группе
                if (isset($inGroup[$lesson->group_id])) {
                    if ($lesson->date >= $minConductedDate[$lesson->group_id]) {
                        $schedule->push($lesson);
                    }
                } else {
                    // человек НЕ в группе
                    if (
                        $lesson->date >= $minConductedDate[$lesson->group_id]
                        && $lesson->date <= $maxConductedDate[$lesson->group_id]
                    ) {
                        $schedule->push($lesson);
                    }
                }
            } elseif ($lesson->date >= now()->format('Y-m-d')) {
                $schedule->push($lesson);
            }
        }

        return $schedule;
    }

    public function getScheduleQuery(int $year)
    {
        $cvpIds = $this->getContractVersionProgramIds($year);

        /**
         * Все занятия из групп, в которых клиент в данный момент находится.
         * Или все занятия, на которых ученик реально присутствовал
         */
        return Lesson::query()->where(
            fn ($q) => $q->whereExists(fn ($q) => $q->selectRaw(1)
                ->from('client_groups as cg')
                ->whereColumn('cg.group_id', 'lessons.group_id')
                ->whereIn('cg.contract_version_program_id', $cvpIds)
            )->orWhereExists(fn ($q) => $q->selectRaw(1)
                ->from('client_lessons as cl')
                ->whereColumn('cl.lesson_id', 'lessons.id')
                ->whereIn('cl.contract_version_program_id', $cvpIds)
            )
        );
    }

    /**
     * Получить все contract_version_program_id из активных версий договора
     */
    public function getContractVersionProgramIds(int $year): Collection
    {
        return $this->contracts()
            ->where('year', $year)
            ->join('contract_versions as cv',
                fn ($join) => $join
                    ->on('cv.contract_id', '=', 'contracts.id')
                    ->where('cv.is_active', true)
            )
            ->join(
                'contract_version_programs as cvp',
                'cvp.contract_version_id',
                '=',
                'cv.id'
            )
            ->pluck('cvp.id');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class)->orderBy('id', 'desc');
    }

    /**
     * Могут логиниться те, у кого есть cvp в статусе
     */
    public function scopeCanLogin($query)
    {
        return $query->whereHas('contracts', fn ($q) => $q
            ->where('year', '>=', current_academic_year())
            ->whereHas('versions', fn ($q) => $q
                ->where('is_active', true)
                ->whereHas('programs', fn ($q) => $q
                    ->whereIn('status', CvpStatus::getActiveStatuses())
                )
            )
        );
    }

    public function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with('phones');
    }

    public function getPassportAttribute($value)
    {
        return $value === null ? [
            'series' => null,
            'number' => null,
            'birthdate' => null,
        ] : json_decode($value);
    }

    public function getHasGradesAttribute(): bool
    {
        return $this->grades()->exists();
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function scheduleDrafts(): HasMany
    {
        return $this->hasMany(ScheduleDraft::class);
    }

    /**
     * Год: все направления клиента в этом году
     * Все направления без учета активна / неактивна программа
     *
     * @return object<int, Direction[]>
     */
    public function getDirectionsAttribute(): object
    {
        $years = $this->contracts->pluck('year')->sort()->values();
        $result = (object) [];

        foreach ($years as $year) {
            $result->$year = $this->contracts
                ->where('year', $year)
                ->map(fn ($c) => $c->active_version)
                ->map(fn ($activeVersion) => $activeVersion->directions)
                ->flatten()
                ->unique()
                ->values()
                ->all();
        }

        return $result;
    }

    public function getSearchWeight(): int
    {
        return intval($this->contracts()->max('year') ?? 1000);
    }

    public function getLastSeenAtAttribute(): ?string
    {
        return $this->logs
            ->whereNull('client_parent_id')
            ->whereNull('emulation_user_id')
            ->max('created_at');
    }
}
