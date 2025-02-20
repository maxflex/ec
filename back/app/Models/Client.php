<?php

namespace App\Models;

use App\Contracts\CanLogin;
use App\Contracts\HasTeeth;
use App\Enums\Direction;
use App\Enums\LessonStatus;
use App\Traits\HasName;
use App\Traits\HasPhones;
use App\Traits\HasPhoto;
use App\Traits\HasTelegramMessages;
use App\Traits\RelationSyncable;
use App\Utils\Teeth;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Scout\Searchable;

class Client extends Authenticatable implements CanLogin, HasTeeth
{
    use HasName, HasPhones, HasPhoto, HasTelegramMessages, RelationSyncable, Searchable;

    protected $fillable = [
        'first_name', 'last_name', 'middle_name', 'branches',
        'head_teacher_id', 'passport', 'is_remote', 'email',
    ];

    protected $casts = [
        'passport' => 'array',
        'is_remote' => 'bool',
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

    public function getRequestsAttribute()
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
     * Получить все активные contract_version_program_id
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

    public function scopeCanLogin($query)
    {
        // tmp duplication
        // сумма занятий и цен из ContractVersionProgramPrice
        $totals = DB::table('contract_version_program_prices')
            ->selectRaw('
                contract_version_program_id,
                CAST(SUM(`lessons`) AS UNSIGNED) AS total_lessons,
                CAST(SUM(`price` * `lessons`) AS UNSIGNED) AS total_price
            ')
            ->groupBy('contract_version_program_id');

        // сумма цен за проведённые занятия из ClientLesson
        $totalPassed = DB::table('client_lessons')
            ->selectRaw('
                contract_version_program_id,
                CAST(SUM(`price`) AS UNSIGNED) AS total_price_passed
            ')
            ->groupBy('contract_version_program_id');

        /**
         * Программы последней версии договора
         */
        $swampsCte = DB::table('contract_version_programs', 'cvp')
            ->join('contract_versions as cv', fn ($join) => $join
                ->on('cv.id', '=', 'cvp.contract_version_id')
                ->where('cv.is_active', true)
            )
            ->join('contracts as c', 'c.id', '=', 'cv.contract_id')
            ->joinSub($totals, 't', 't.contract_version_program_id', '=', 'cvp.id')
            ->leftJoinSub($totalPassed, 'tp', 'tp.contract_version_program_id', '=', 'cvp.id')
            ->leftJoin(
                'client_groups as cg',
                'cg.contract_version_program_id',
                '=',
                'cvp.id'
            )
            ->selectRaw('
                cvp.id,
                t.total_lessons,
                t.total_price,
                CAST(IFNULL(tp.total_price_passed, 0) AS UNSIGNED) AS `total_price_passed`,
                `group_id`,
                cv.contract_id,
                c.year,
                c.client_id,
                cvp.program
            ');

        $query->whereHas('contracts', fn ($q) => $q
            ->whereIn('contracts.year', [current_academic_year(), current_academic_year() + 1])
            ->whereRaw('EXISTS (
                SELECT 1 FROM s
                WHERE s.contract_id = contracts.id
                AND (s.group_id IS NOT NULL OR total_price_passed < total_price)
            )')
            // 2-3 => оставить тех, кто: либо в группе, либо услуги по договору не оказаны
        )
            ->withExpression('s', $swampsCte)
            ->groupByRaw('clients.id');
    }

    // Upd. По результату телефонного разговора:
    //  1) договоры только текущий год
    //  2) отсеять: нет в группах
    //  3) отсеять: услуги по договору оказаны
    // 2-3 => оставить тех, кто: либо в группе, либо услуги по договору не оказаны

    public function searchableAs()
    {
        return 'people';
    }

    public function toSearchableArray()
    {
        $class = class_basename(self::class);
        $weight = intval($this->contracts()->max('year') ?? 1000);

        return [
            'id' => implode('-', [$class, $this->id]),
            'first_name' => $this->first_name ? mb_strtolower($this->first_name) : '',
            'last_name' => $this->last_name ? mb_strtolower($this->last_name) : '',
            'middle_name' => $this->middle_name ? mb_strtolower($this->middle_name) : '',
            'phones' => $this->phones()->pluck('number'),
            'contract_ids' => $this->contracts()->pluck('id')->map(fn ($e) => (string) $e)->all(),
            'is_active' => Client::canLogin()->whereId($this->id)->exists(),
            'weight' => $weight,
        ];
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
     * Для этого из всех договоров берем договоры последнего года (их может быть несколько).
     * Берем у договора(ов) по последней версии и выдаем направления по этой версии
     *
     * @return Direction[]
     */
    public function getDirectionsAttribute(): array
    {
        $contracts = $this->contracts;
        $maxYear = $contracts->max('year');

        return $contracts
            ->where('year', $maxYear)
            ->map(fn ($c) => $c->active_version)
            ->map(fn ($activeVersion) => $activeVersion->directions)
            ->flatten()
            ->unique()
            ->all();
    }
}
