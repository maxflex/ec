<?php

namespace App\Models;

use App\Traits\HasName;
use App\Traits\HasPhones;
use App\Traits\HasPhoto;
use App\Traits\HasTelegramMessages;
use App\Traits\RelationSyncable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Client extends Model
{
    use HasName, HasPhones, HasPhoto, HasTelegramMessages, RelationSyncable;

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
     * @return ContractVersionProgram[]
     */
    public function swamps(): Attribute
    {
        $programs = $this->groups->pluck('program');
        $result = [];

        foreach ($this->contracts as $contract) {
            foreach ($contract->versions->last()->programs as $p) {
                if (!$programs->contains($p->program)) {
                    $result[] = $p;
                }
            }
        }
        return Attribute::make(fn () => $result);
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
        $fact = [];
        $contractLessons = ContractLesson::whereIn('contract_id', $contracts->pluck('id'))->get();
        foreach ($contractLessons as $contractLesson) {
            $lesson = $contractLesson->lesson;
            // $lesson->load('contractLessons', fn ($q) => $q->whereId($contractLesson->id));
            $lesson->contractLesson = $contractLesson;
            $schedule->push($lesson);
            $fact[$lesson->id] = true;
        }

        foreach ($contracts as $contract) {
            foreach ($contract->groups as $group) {
                foreach ($group->lessons as $lesson) {
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
}
