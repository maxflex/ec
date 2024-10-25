<?php

namespace App\Models;

use App\Contracts\{CanLogin, HasTeeth};
use App\Enums\TeacherStatus;
use App\Traits\{HasBalance, HasName, HasPhones, HasPhoto, HasTelegramMessages, RelationSyncable};
use App\Utils\Teeth;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Laravel\Scout\Searchable;

class Teacher extends Model implements HasTeeth, CanLogin
{
    use HasPhones, HasPhoto, HasTelegramMessages, RelationSyncable, Searchable, HasName, HasBalance;

    protected $fillable = [
        'first_name', 'last_name', 'middle_name', 'status', 'subjects',
        'so', 'desc', 'photo_desc', 'passport', 'is_published'
    ];

    protected $casts = [
        'status' => TeacherStatus::class,
        'is_published' => 'bool',
        'passport' => 'array',
    ];

    public function payments(): HasMany
    {
        return $this->hasMany(TeacherPayment::class)->latest();
    }

    public function services(): HasMany
    {
        return $this->hasMany(TeacherService::class)->latest();
    }

    public function signs(): HasMany
    {
        return $this->hasMany(InstructionSign::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function headTeacherReports(): HasMany
    {
        return $this->hasMany(HeadTeacherReport::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subjects(): Attribute
    {
        return Attribute::make(
            fn ($value) => $value ? explode(',', $value) : [],
            fn ($value) => $value ? join(',', $value) : null
        );
    }

    protected function getBalanceItems($year): array
    {
        $balanceItems = [];

        $lessons = Lesson::query()
            ->conducted()
            ->whereHas('group', fn($q) => $q->where('year', $year))
            ->where('teacher_id', $this->id)
            ->get();

        foreach ($lessons as $lesson) {
            $balanceItems[] = (object)[
                'dateTime' => $lesson->conducted_at,
                'sum' => $lesson->price,
                'comment' => sprintf(
                    'занятие %s, группа %d, кабинет %s',
                    Carbon::parse($lesson->dateTime)->format('d.m.y в H:i'),
                    $lesson->group_id,
                    $lesson->cabinet->value
                )
            ];
        }

        $payments = $this->payments()->where('year', $year)->get();
        foreach ($payments as $payment) {
            $balanceItems[] = (object)[
                'dateTime' => $payment->created_at->format('Y-m-d H:i:s'),
                'sum' => $payment->sum * ($payment->is_return ? 1 : -1),
                'comment' => sprintf(
                    '%s (обучение)',
                    $payment->method->getTitle()
                )
            ];
        }

        $services = $this->services()->where('year', $year)->get();
        foreach ($services as $service) {
            $balanceItems[] = (object)[
                'dateTime' => $service->created_at->format('Y-m-d H:i:s'),
                'sum' => $service->sum,
                'comment' => $service->purpose,
            ];
        }

        $reports = $this->reports()->where('year', $year)->where('price', '>', 0)->get();
        foreach ($reports as $report) {
            $balanceItems[] = (object)[
                'dateTime' => $report->created_at->format('Y-m-d H:i:s'),
                'sum' => $report->price,
                'comment' => sprintf(
                    'отчет по ученику %s',
                    $report->client->formatName()
                )
            ];
        }

        return $balanceItems;
    }

    public function scopeActive($query): void
    {
        $query->where('status', TeacherStatus::active);
    }

    public function scopeCanLogin($query)
    {
        $query->active();
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
            'first_name' => $this->first_name ?? '',
            'last_name' => $this->last_name ?? '',
            'middle_name' => $this->middle_name ?? '',
            'phones' => $this->phones()->pluck('number'),
            'is_active' => $this->status === TeacherStatus::active,
            'weight' => 499 + intval($this->lessons()->count() / 3),
        ];
    }


    /**
     * Получить преподов, у которых есть хоть какие-то платежи
     *
     */
    public function scopeWithPayments($query, int $year)
    {
        $queries = [
            Lesson::join('groups', 'groups.id', '=', 'lessons.group_id'),
            Report::query(),
            TeacherPayment::query(),
            TeacherService::query()
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
}
