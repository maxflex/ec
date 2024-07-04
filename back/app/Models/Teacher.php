<?php

namespace App\Models;

use App\Enums\TeacherStatus;
use App\Traits\HasPhones;
use App\Traits\HasPhoto;
use App\Traits\RelationSyncable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Teacher extends Model
{
    use HasPhones, HasPhoto, RelationSyncable;

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

    public function reports()
    {
        return $this->hasMany(Report::class);
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

        $payments = $this->payments()->where('year', $year)->get();

        $balanceItems = collect();

        foreach ($lessons as $lesson) {
            $balanceItems->push((object) [
                'dateTime' => $lesson->conducted_at,
                'sum' => $lesson->price,
                'comment' => sprintf(
                    'занятие %s, группа %d, кабинет %s',
                    Carbon::parse($this->start_at)->format('d.m.y в H:i'),
                    $lesson->group_id,
                    $lesson->cabinet->value
                )
            ]);
        }

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

        $balanceItemsGroupped = $balanceItems
            ->sort(fn ($a, $b) => $a->dateTime > $b->dateTime)
            ->groupBy(fn ($item) => str($item->dateTime)->before(' '));

        $data = [];
        $balance = 0;
        foreach ($balanceItemsGroupped as $date => $balanceItems) {
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


    public function getSchedule(int $year): Collection
    {
        $schedule = Lesson::query()
            ->whereHas('group', fn ($q) => $q->where('year', $year))
            ->where('teacher_id', $this->id)
            ->get();

        return $schedule->unique(fn ($l) => $l->id);
    }
}
