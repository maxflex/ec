<?php

namespace App\Models;

use App\Contracts\HasTeeth;
use App\Enums\TeacherPaymentMethod;
use App\Enums\TeacherStatus;
use App\Traits\IsSearchable;
use App\Utils\Balance\Balance;
use App\Utils\Teeth;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Person implements HasTeeth
{
    use IsSearchable;

    protected $fillable = [
        'first_name', 'last_name', 'middle_name', 'status', 'subjects',
        'so', 'desc', 'photo_desc', 'passport', 'is_published', 'is_split_balance',
    ];

    protected $casts = [
        'status' => TeacherStatus::class,
        'is_split_balance' => 'bool',
        'is_published' => 'bool',
        'passport' => 'array',
        'stats' => 'array',
    ];

    public function clientComplaints(): HasMany
    {
        return $this->hasMany(ClientComplaint::class);
    }

    public function signs(): HasMany
    {
        return $this->hasMany(InstructionSign::class);
    }

    public function headTeacherReports(): HasMany
    {
        return $this->hasMany(HeadTeacherReport::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subjects(): Attribute
    {
        return Attribute::make(
            fn ($value) => $value ? explode(',', $value) : [],
            fn ($value) => $value ? implode(',', $value) : null
        );
    }

    public function scopeCanLogin($query)
    {
        $query->where('status', TeacherStatus::active);
    }

    public function getTeeth(?int $year = null): object
    {
        if (is_null($year)) {
            throw new Exception('Year is required');
        }

        $query = Lesson::query()
            ->where('teacher_id', $this->id)
            ->whereHas('group', fn ($q) => $q->where('year', $year));

        return Teeth::get($query);
    }

    public function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with('phones');
    }

    /**
     * Получить преподов, у которых есть хоть какие-то платежи
     */
    public function scopeWithPayments($query, int $year)
    {
        $queries = [
            Lesson::join('groups', 'groups.id', '=', 'lessons.group_id'),
            Report::query(),
            TeacherPayment::query(),
            TeacherService::query(),
        ];

        $teacherIds = collect();
        foreach ($queries as $q) {
            $teacherIds = $teacherIds->merge(
                $q->where('year', $year)->pluck('teacher_id')
            );
        }

        $query->whereIn('id', $teacherIds->unique());
    }

    public function getIsHeadTeacherAttribute(): bool
    {
        return Client::where('head_teacher_id', $this->id)->exists();
    }

    public function getPassportAttribute($value)
    {
        return $value === null ? [
            'series' => null,
            'number' => null,
            'address' => null,
            'code' => null,
            'issued_by' => null,
        ] : json_decode($value);
    }

    public function getSearchWeight(): int
    {
        return 499 + intval($this->lessons()->count() / 3);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    public function getBalance(int $year, ?bool $split = null): Balance
    {
        $balance = new Balance;

        if ($split !== false) {
            $lessons = Lesson::query()
                ->conducted()
                ->whereHas('group', fn ($q) => $q->where('year', $year))
                ->where('teacher_id', $this->id)
                ->get();

            foreach ($lessons as $lesson) {
                $balance->push(
                    $lesson->price,
                    $lesson->date_time,
                    sprintf(
                        'занятие %s, группа %d, кабинет %s, %s',
                        $lesson->date_time->format('в H:i'),
                        $lesson->group_id,
                        filter_var($lesson->cabinet->value, FILTER_SANITIZE_NUMBER_INT),
                        $lesson->group->program->getShortName(),
                    )
                );
            }
        }

        $query = $this->payments()->where('year', $year);

        if ($split === true) {
            $query->where('method', TeacherPaymentMethod::bill);
        }
        if ($split === false) {
            $query->where('method', '<>', TeacherPaymentMethod::bill);
        }

        foreach ($query->get() as $payment) {
            $balance->push(
                $payment->sum * ($payment->is_return ? 1 : -1),
                $payment->created_at->format('Y-m-d H:i:s'),
                sprintf(
                    'выплата, %s',
                    $payment->method->getTitle()
                ),
            );
        }

        if ($split !== true) {
            $services = $this->services()->where('year', $year)->get();
            foreach ($services as $service) {
                $balance->push(
                    $service->sum,
                    $service->date.' 15:00:00',
                    $service->purpose,
                );
            }

            $reports = $this->reports()->where('year', $year)->where('price', '>', 0)->get();
            foreach ($reports as $report) {
                $balance->push(
                    $report->price,
                    $report->created_at->format('Y-m-d H:i:s'),
                    sprintf(
                        'отчет по ученику %s, %s',
                        $report->client->formatName(),
                        $report->program->getShortName(),
                    ),
                );
            }
        }

        return $balance;
    }

    public function payments(): HasMany
    {
        return $this->hasMany(TeacherPayment::class)->latest();
    }

    public function services(): HasMany
    {
        return $this->hasMany(TeacherService::class)->latest();
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }
}
