<?php

namespace App\Models;

use App\Contracts\HasTeeth;
use App\Enums\TeacherPaymentMethod;
use App\Enums\TeacherStatus;
use App\Traits\HasBalance;
use App\Traits\HasTelegramMessages;
use App\Utils\Teeth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Teacher extends Person implements HasTeeth
{
    use HasBalance, HasTelegramMessages, Searchable;

    protected $fillable = [
        'first_name', 'last_name', 'middle_name', 'status', 'subjects',
        'so', 'desc', 'photo_desc', 'passport', 'is_published', 'is_split_balance',
    ];

    protected $casts = [
        'status' => TeacherStatus::class,
        'is_split_balance' => 'bool',
        'is_published' => 'bool',
        'passport' => 'array',
    ];

    public function scholarshipScores(): HasMany
    {
        return $this->hasMany(ScholarshipScore::class);
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

    public function getTeeth(int $year): object
    {
        $query = Lesson::query()->where('teacher_id', $this->id);

        return Teeth::get($query, $year);
    }

    public function searchableAs()
    {
        return 'people';
    }

    public function toSearchableArray()
    {
        $class = class_basename(self::class);

        return [
            'id' => implode('-', [$class, $this->id]),
            'first_name' => $this->first_name ? mb_strtolower($this->first_name) : '',
            'last_name' => $this->last_name ? mb_strtolower($this->last_name) : '',
            'phones' => $this->phones->pluck('number')->toArray(),
            'is_active' => Teacher::canLogin()->whereId($this->id)->exists(),
            'weight' => 499 + intval($this->lessons()->count() / 3),
        ];
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
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

    protected function getBalanceItems($year, ?bool $split = null): array
    {
        $balanceItems = [];

        if ($split !== false) {
            $lessons = Lesson::query()
                ->conducted()
                ->whereHas('group', fn ($q) => $q->where('year', $year))
                ->where('teacher_id', $this->id)
                ->get();

            foreach ($lessons as $lesson) {
                $balanceItems[] = (object) [
                    'dateTime' => $lesson->date_time,
                    'sum' => $lesson->price,
                    'comment' => sprintf(
                        'занятие %s, группа %d, кабинет %s, %s',
                        $lesson->date_time->format('в H:i'),
                        $lesson->group_id,
                        filter_var($lesson->cabinet->value, FILTER_SANITIZE_NUMBER_INT),
                        $lesson->group->program->getShortName(),
                    ),
                ];
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
            $balanceItems[] = (object) [
                'dateTime' => $payment->created_at->format('Y-m-d H:i:s'),
                'sum' => $payment->sum * ($payment->is_return ? 1 : -1),
                'comment' => sprintf(
                    'выплата, %s',
                    $payment->method->getTitle()
                ),
            ];
        }

        if ($split !== true) {
            $services = $this->services()->where('year', $year)->get();
            foreach ($services as $service) {
                $balanceItems[] = (object) [
                    'dateTime' => $service->date.' 15:00:00',
                    'sum' => $service->sum,
                    'comment' => $service->purpose,
                ];
            }

            $reports = $this->reports()->where('year', $year)->where('price', '>', 0)->get();
            foreach ($reports as $report) {
                $balanceItems[] = (object) [
                    'dateTime' => $report->created_at->format('Y-m-d H:i:s'),
                    'sum' => $report->price,
                    'comment' => sprintf(
                        'отчет по ученику %s, %s',
                        $report->client->formatName(),
                        $report->program->getShortName(),
                    ),
                ];
            }
        }

        return $balanceItems;
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
