<?php

namespace App\Models;

use App\Contracts\CanLogin;
use App\Contracts\HasTeeth;
use App\Traits\{HasName, HasPhones, HasPhoto, HasTelegramMessages, RelationSyncable};
use App\Utils\Teeth;
use Illuminate\Database\Eloquent\{Casts\Attribute,
    Relations\BelongsTo,
    Relations\HasMany,
    Relations\HasOne,
    Relations\MorphMany};
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;
use Laravel\Scout\Searchable;

class Client extends Authenticatable implements HasTeeth, CanLogin
{
    use HasPhones, HasPhoto, HasTelegramMessages, RelationSyncable, Searchable, HasName;

    protected $fillable = [
        'first_name', 'last_name', 'middle_name', 'branches',
        'head_teacher_id', 'passport', 'is_remote',
    ];

    protected $casts = [
        'passport' => 'array',
        'is_remote' => 'bool',
    ];

    public function tests(): HasMany
    {
        return $this->hasMany(ClientTest::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class)->orderBy('id', 'desc');
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
            fn ($value) => $value ? join(',', $value) : null
        );
    }

    public function headTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'head_teacher_id');
    }

    public function getRequestsAttribute()
    {
        $numbers = $this->phones->pluck('number')->unique();
        return Request::whereHas('phones', fn($q) => $q->whereIn('number', $numbers))->get()->all();
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

        // фактически проведённые
        $fact = [];
        $clientLessons = ClientLesson::query()->whereHas(
            'contractVersionProgram.contractVersion.contract',
            fn($q) => $q->where('client_id', $this->id)->where('year', $year)
        )->get();
        foreach ($clientLessons as $clientLesson) {
            $lesson = $clientLesson->lesson;
            // $lesson->load('clientLessons', fn ($q) => $q->whereId($clientLesson->id));
            $lesson->setAttribute('client_lesson', $clientLesson);
            $schedule->push($lesson);
            $fact[$lesson->id] = true;
        }

        $contracts = $this->contracts()->where('year', $year)->get();
        foreach ($contracts as $contract) {
            $programs = $contract->getActiveVersion()
                ->programs()
                ->whereHas('clientGroup')
                ->get();
            foreach ($programs as $program) {
                foreach ($program->group->lessons as $lesson) {
                    // пропускаем фактически проведённые
                    if (isset($fact[$lesson->id])) {
                        continue;
                    }
                    $schedule->push($lesson);
                }
            }
        }

        return $schedule;
    }

    /**
     * Получить все активные contract_version_program_id
     */
    public function getContractVersionProgramIds()
    {
        return $this->contracts()
            ->join('contract_versions as cv',
                fn($join) => $join
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

    public function scopeActive($query): void
    {
        $query->whereHas('contracts');
    }

    public function scopeCanLogin($query)
    {
        $query->whereHas('contracts', fn($q) => $q->whereRaw("
            IF(
                MONTH(CURDATE()) BETWEEN 1 AND 7, 
                -- январь-июль
                `year` BETWEEN YEAR(CURDATE()) - 1 AND YEAR(CURDATE()),
                -- август-декабрь
                `year` = YEAR(CURDATE())
            )
        "));
    }

    public function searchableAs()
    {
        return 'people';
    }

    public function toSearchableArray()
    {
        $class = class_basename(self::class);
        $maxContractYear = intval($this->contracts()->max('year') ?? 1000);

        return [
            'id' => implode('-', [$class, $this->id]),
            'first_name' => $this->first_name ?? '',
            'last_name' => $this->last_name ?? '',
            'middle_name' => $this->middle_name ?? '',
            'phones' => $this->phones()->pluck('number'),
            'is_active' => $maxContractYear === current_academic_year(),
            'weight' => $maxContractYear,
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
}
