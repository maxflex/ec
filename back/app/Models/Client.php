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
     * TODO: remove
     * @return Group[]
     */
    public function getGroupsAttribute()
    {
        return [];
//        return Group::whereHas(
//            'clientGroups',
//            fn ($q) => $q->whereIn('contract_id', $this->contracts()->pluck('id'))
//        )->get();
    }

    /**
     * TODO: remove
     * @return array
     */
    public function getSwampsAttribute()
    {
        return [];
//        $programs = $this->groups->pluck('program');
//        $result = [];
//
//        foreach ($this->contracts as $contract) {
//            foreach ($contract->versions->last()->programs as $p) {
//                if (!$programs->contains($p->program)) {
//                    $result[] = $p;
//                }
//            }
//        }
//        return Attribute::make(fn () => $result);
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

        // фактически проведённые
        $fact = [];
        $clientLessons = ClientLesson::query()->whereHas(
            'contractVersionProgram.contractVersion.contract',
            fn($q) => $q->where('client_id', $this->id)->where('year', $year)
        );
        foreach ($clientLessons as $clientLesson) {
            $lesson = $clientLesson->lesson;
            // $lesson->load('clientLessons', fn ($q) => $q->whereId($clientLesson->id));
            $lesson->clientLesson = $clientLesson;
            $schedule->push($lesson);
            $fact[$lesson->id] = true;
        }

        $contracts = $this->contracts()->where('year', $year)->get();
        foreach ($contracts as $contract) {
            $programs = $contract->getActiveVersion()
                ->programs()
                ->whereHas('clientGroup')
                ->get();
            foreach ($programs as $program) {
                foreach ($program->group->lessons as $lesson) {
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
        $programIds = $this->contracts()
            ->join('contract_versions as cv', fn($join) => $join
                ->on('cv.contract_id', '=', 'contracts.id')
                ->where('cv.is_active', true)
            )
            ->join(
                'contract_version_programs as cvp',
                'cvp.contract_version_id',
                '=',
                'cv.id'
            )
            ->pluck('cvp.id');
        $query = Lesson::query()
            ->join('client_groups as cg', 'cg.group_id', '=', 'lessons.group_id')
            ->whereIn(
                'cg.contract_version_program_id',
                $programIds
            );
        return Teeth::get($query, $year);
    }

    public function scopeActive($query): void
    {
        $query->whereHas('contracts');
    }
}
