<?php

namespace App\Models;

use App\Enums\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Contract extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'year', 'company', 'client_id'
    ];

    protected $casts = [
        'company' => Company::class,
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function versions()
    {
        return $this->hasMany(ContractVersion::class)->orderBy('version', 'desc');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }

    public function payments()
    {
        return $this->hasMany(ContractPayment::class);
    }


    public function getBalance()
    {
        $contractLessons = ContractLesson::query()
            ->where('contract_id', $this->id)
            ->get();

        $balanceItems = collect();

        foreach ($contractLessons as $contractLesson) {
            $lesson = $contractLesson->lesson;
            $balanceItems->push((object) [
                'dateTime' => $lesson->conducted_at,
                'sum' => $contractLesson->price * -1,
                'comment' => sprintf(
                    'занятие %s, группа %d, кабинет %s',
                    Carbon::parse($lesson->dateTime)->format('d.m.y в H:i'),
                    $lesson->group_id,
                    $lesson->cabinet->value
                )
            ]);
        }

        foreach ($this->payments as $payment) {
            $balanceItems->push((object) [
                'dateTime' => $payment->created_at->format('Y-m-d H:i:s'),
                'sum' => $payment->sum * ($payment->is_return ? -1 : 1),
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
}
