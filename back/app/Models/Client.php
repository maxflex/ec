<?php

namespace App\Models;

use App\Traits\HasPhones;
use App\Traits\RelationSyncable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Client extends Model
{
    use HasPhones, RelationSyncable;

    protected $fillable = [
        'first_name', 'last_name', 'middle_name', 'branches',
        'head_teacher_id'
    ];

    // public $interfaces = [
    //     'groups' => ['type' => 'Groups'],
    //     'swamps' => ['type' => 'ContractPrograms'],
    //     'tests' => ['type' => 'ClientTests'],
    //     'head_teacher' => ['type' => 'Teacher | null'],
    //     'branches' => ['type' => 'string[]'],
    // ];

    protected $hidden = ['groups', 'swamps'];

    public function tests()
    {
        return $this->hasMany(ClientTest::class);
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class)->orderBy('id', 'desc');
    }

    public function contractGroup()
    {
        return $this->hasManyThrough(ContractGroup::class, Contract::class);
    }

    public function payments()
    {
        return $this->morphMany(ClientPayment::class, 'entity');
    }

    public function parent()
    {
        return $this->hasOne(ClientParent::class);
    }

    /**
     * @return Group[]
     */
    public function groups(): Attribute
    {
        return Attribute::make(
            fn (): Collection =>
            $this->contractGroup()->with('group')->get()->map(fn ($e) => $e->group)
        );
    }

    /**
     * @return ContractProgram[]
     */
    public function swamps(): Attribute
    {
        return Attribute::make(fn () => []);
        // $programs = $this->groups->pluck('program');
        // $result = [];

        // foreach ($this->contracts as $contract) {
        //     foreach ($contract->versions[0]->programs as $p) {
        //         if (!$programs->contains($p->program)) {
        //             $result[] = $p;
        //         }
        //     }
        // }
        // return Attribute::make(fn () => $result);
    }

    public function branches(): Attribute
    {
        return Attribute::make(
            fn ($value) => $value ? explode(',', $value) : [],
            fn ($value) => $value ? join(',', $value) : null
        );
    }

    public function headTeacher()
    {
        return $this->belongsTo(Teacher::class, 'head_teacher_id');
    }

    /**
     * @return array<Request>
     */
    public function requests(): Attribute
    {
        $numbers = $this->phones->pluck('number')->unique();
        $requests = Request::whereHas('phones', fn ($q) => $q->whereIn('number', $numbers))->get()->all();
        return Attribute::make(
            fn () => $requests
        );
    }


    public function getBalance(int $year)
    {
        $contractIds = $this->contracts()->where('year', $year)->pluck('id');
        $contractLessons = ContractLesson::whereIn('contract_id', $contractIds)->get();

        $balanceItems = collect();

        foreach ($contractLessons as $contractLesson) {
            $lesson = $contractLesson->lesson;
            $balanceItems->push((object) [
                'dateTime' => $lesson->conducted_at,
                'sum' => $contractLesson->price * -1,
                'comment' => sprintf(
                    'занятие %s, группа %d, кабинет %s',
                    Carbon::parse($lesson->start_at)->format('d.m.y в H:i'),
                    $lesson->group_id,
                    $lesson->cabinet->value
                )
            ]);
        }

        $payments = ClientPayment::query()
            ->where('entity_type', Contract::class)
            ->whereIn('entity_id', $contractIds)
            ->where('year', $year)
            ->get();

        foreach ($payments as $payment) {
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
