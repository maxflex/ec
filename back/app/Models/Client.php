<?php

namespace App\Models;

use App\Contracts\HasTeeth;
use App\Enums\ContractVersionProgramStatus;
use App\Enums\Direction;
use App\Enums\HeadAboutUs;
use App\Enums\LessonStatus;
use App\Traits\IsSearchable;
use App\Utils\Teeth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

class Client extends Person implements HasTeeth
{
    use IsSearchable;

    protected $fillable = [
        'first_name', 'last_name', 'middle_name', 'branches',
        'head_teacher_id', 'passport', 'is_remote', 'email',
        'heard_about_us', 'mark_sheet',
    ];

    protected $casts = [
        'passport' => 'array',
        'is_remote' => 'bool',
        'mark_sheet' => 'array',
        'heard_about_us' => HeadAboutUs::class,
    ];

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
    public function getSchedule(int $year): Collection
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

    public function getTeeth(int $year): object
    {
        $query = Lesson::query()
            ->join('client_groups as cg', 'cg.group_id', '=', 'lessons.group_id')
            ->whereIn(
                'cg.contract_version_program_id',
                $this->getContractVersionProgramIds(),
            );

        return Teeth::get($query, $year);
    }

    /**
     * Получить все contract_version_program_id из активной версии договора
     */
    public function getContractVersionProgramIds()
    {
        return $this->contracts()
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
            ->whereHas('versions.programs', fn ($q) => $q
                ->whereIn('status', [
                    ContractVersionProgramStatus::toFulfil,
                    ContractVersionProgramStatus::inProcess,
                    ContractVersionProgramStatus::finishedInGroup,
                ])
            )
        );
    }

    public function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query
            ->with('phones')
            ->whereRaw('`created_at` >= NOW() - INTERVAL 5 YEAR');
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

    /**
     * Направления клиента
     *
     * Если у клиента есть договоры текущего учебного года, берём их
     * Если нет, то берем договоры последнего года
     * Договоров может быть несколько в цепи, используем их все для вычисления направления
     *
     * @return Direction[]
     */
    public function getDirectionsAttribute(): array
    {
        $year = $this->contracts->where('year', current_academic_year())->count()
            ? current_academic_year()
            : $this->contracts->max('year');

        return $this->contracts
            ->where('year', $year)
            ->map(fn ($c) => $c->active_version)
            ->map(fn ($activeVersion) => $activeVersion->directions)
            ->flatten()
            ->unique()
            ->all();
    }

    /**
     * Направления текущего учебного года
     * (используется на /people-selector)
     */
    public function getCurrentYearDirectionsAttribute()
    {
        return $this->contracts
            ->where('year', current_academic_year())
            ->map(fn ($c) => $c->active_version)
            ->map(fn ($activeVersion) => $activeVersion->directions)
            ->flatten()
            ->unique()
            ->all();
    }

    public function getSearchWeight(): int
    {
        return intval($this->contracts()->max('year') ?? 1000);
    }
}
