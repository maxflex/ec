<?php

namespace App\Models;

use App\Traits\HasPhones;
use App\Traits\HasPhoto;
use App\Traits\RelationSyncable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Client extends Model
{
    use HasPhones, HasPhoto, RelationSyncable;

    protected $fillable = [
        'first_name', 'last_name', 'middle_name', 'branches',
        'head_teacher_id'
    ];

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

    /**
     * фактически проведенные занятия
     * занятия прошедшие без ученика в группе, в которой ученик сейчас присутствует
     * отмененные занятия в группе, в которой ученик сейчас присутствует
     * планируемые занятия в группе, в которой ученик сейчас присутствует
     */
    public function getSchedule(int $year): Collection
    {
        $schedule = collect();
        $contracts = $this->contracts()->where('year', $year)->get();

        // фактически проведённые
        ContractLesson::whereIn('contract_id', $contracts->pluck('id'))->get()->each(
            function ($e) use ($schedule) {
                $lesson = $e->lesson;
                $lesson->contractLesson = extract_fields($e, [
                    'price', 'status', 'minutes_late'
                ]);
                $schedule->push($lesson);
            }
        );

        foreach ($contracts as $contract) {
            foreach ($contract->groups as $group) {
                $schedule = $schedule->merge($group->lessons);
            }
        }

        return $schedule
            ->unique(fn ($e) => $e->id)
            ->transform(fn ($e) => extract_fields($e, [
                'date', 'time', 'status', 'contractLesson'
            ], [
                'group' => extract_fields($e->group, [
                    'program'
                ])
            ]))
            ->groupBy('date');
    }
}
