<?php

namespace App\Models;

use App\Contracts\{HasTeeth};
use App\Enums\TeacherStatus;
use App\Traits\{HasBalance, HasName, HasPhones, HasPhoto, HasTelegramMessages, RelationSyncable};
use App\Utils\Teeth;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Laravel\Scout\Searchable;

class Teacher extends Model implements HasTeeth
{
    use HasPhones, HasPhoto, HasTelegramMessages, RelationSyncable, Searchable, HasName, HasBalance;

    protected $fillable = [
        'first_name', 'last_name', 'middle_name', 'status', 'subjects',
        'so', 'desc', 'photo_desc', 'passport_series', 'passport_number',
        'passport_address', 'passport_code', 'passport_issued_by'
    ];

    protected $casts = [
        'status' => TeacherStatus::class
    ];

    public function payments()
    {
        return $this->hasMany(TeacherPayment::class)->latest();
    }

    public function services()
    {
        return $this->hasMany(TeacherService::class)->latest();
    }

    public function signs()
    {
        return $this->hasMany(InstructionSign::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function user()
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
}
