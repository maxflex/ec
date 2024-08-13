<?php

namespace App\Models;

use App\Contracts\HasTeeth;
use App\Enums\TeacherStatus;
use App\Traits\HasName;
use App\Traits\HasPhones;
use App\Traits\HasPhoto;
use App\Traits\RelationSyncable;
use App\Utils\Teeth;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Teacher extends Model implements HasTeeth
{
    use HasName, HasPhones, HasPhoto, RelationSyncable;

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

    public function getBalance(int $year)
    {
        $balanceItems = collect();

        $lessons = Lesson::query()
            ->conducted()
            ->whereRaw(<<<SQL
            (
                select `year` from `groups` g
                where g.id = lessons.group_id
            ) = $year
            SQL)
            ->where('teacher_id', $this->id)
            ->get();
        foreach ($lessons as $lesson) {
            $balanceItems->push((object) [
                'dateTime' => $lesson->conducted_at,
                'sum' => $lesson->price,
                'comment' => sprintf(
                    'занятие %s, группа %d, кабинет %s',
                    Carbon::parse($this->dateTime)->format('d.m.y в H:i'),
                    $lesson->group_id,
                    $lesson->cabinet->value
                )
            ]);
        }

        $payments = $this->payments()->where('year', $year)->get();
        foreach ($payments as $payment) {
            $balanceItems->push((object) [
                'dateTime' => $payment->created_at->format('Y-m-d H:i:s'),
                'sum' => $payment->sum * ($payment->is_return ? 1 : -1),
                'comment' => sprintf(
                    '%s (обучение)',
                    $payment->method->getTitle()
                )
            ]);
        }

        $services = $this->services()->where('year', $year)->get();
        foreach ($services as $service) {
            $balanceItems->push((object) [
                'dateTime' => $service->created_at->format('Y-m-d H:i:s'),
                'sum' => $service->sum,
                'comment' => $service->purpose,
            ]);
        }

        $reports = $this->reports()->where('year', $year)->where('price', '>', 0)->get();
        foreach ($reports as $report) {
            $balanceItems->push((object) [
                'dateTime' => $report->created_at->format('Y-m-d H:i:s'),
                'sum' => $report->price,
                'comment' => sprintf(
                    'отчет по ученику %s',
                    $report->client->formatName()
                )
            ]);
        }

        $balanceItemsGrouped = $balanceItems
            ->sort(fn ($a, $b) => $a->dateTime > $b->dateTime)
            ->groupBy(fn ($item) => str($item->dateTime)->before(' '));

        $data = [];
        $balance = 0;
        foreach ($balanceItemsGrouped as $date => $balanceItems) {
            $balance += $balanceItems->sum(fn ($e) => $e->sum);
            $data[] = (object) [
                'date' => $date,
                'balance' => $balance,
                'items' => $balanceItems->map(fn ($e) => (object) [
                    'sum' => $e->sum,
                    'comment' => $e->comment,
                ])->all()
            ];
        }

        return array_reverse($data);
    }

    public function scopeActive($query): void
    {
        $query->where('status', TeacherStatus::active);
    }

    public function getSchedule(int $year): Collection
    {
        $schedule = Lesson::query()
            ->whereHas('group', fn ($q) => $q->where('year', $year))
            ->where('teacher_id', $this->id)
            ->get();

        return $schedule->unique(fn ($l) => $l->id);
    }

    public function getTeeth(int $year): object
    {
        $query = Lesson::where('teacher_id', $this->id);
        return Teeth::get($query, $year);
    }
}
