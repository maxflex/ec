<?php

namespace App\Models;

use App\Contracts\HasTeeth;
use App\Traits\HasName;
use App\Traits\HasPhones;
use App\Traits\HasPhoto;
use App\Traits\HasTelegramMessages;
use App\Traits\RelationSyncable;
use App\Utils\Teeth;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Client extends Model implements HasTeeth
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
    public function getGroupsAttribute()
    {
        return Group::whereHas(
            'clientGroups',
            fn ($q) => $q->whereIn('contract_id', $this->contracts()->pluck('id'))
        )->get();
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
     * Фактически проведенные занятия.
     * Занятия прошедшие без ученика в группе, в которой ученик сейчас присутствует.
     * Отмененные занятия в группе, в которой ученик сейчас присутствует.
     * Планируемые занятия в группе, в которой ученик сейчас присутствует
     */
    public function getSchedule(int $year): Collection
    {
        $schedule = collect();
        $contracts = $this->contracts()->where('year', $year)->get();

        // фактически проведённые
        $fact = [];
        $clientLessons = ClientLesson::whereIn('contract_id', $contracts->pluck('id'))->get();
        foreach ($clientLessons as $clientLesson) {
            $lesson = $clientLesson->lesson;
            // $lesson->load('clientLessons', fn ($q) => $q->whereId($clientLesson->id));
            $lesson->clientLesson = $clientLesson;
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

    public function getTeeth(int $year): object
    {
        $query = Lesson::query()
            ->join('client_groups as gc', 'gc.group_id', '=', 'lessons.group_id')
            ->whereIn('gc.contract_id', $this->contracts()->pluck('id'));
        return Teeth::get($query, $year);
    }

    public function scopeActive($query): void
    {
        $query->whereHas('contracts');
    }
}
