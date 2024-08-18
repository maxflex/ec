<?php

namespace App\Models;

use App\Enums\Program;
use Illuminate\Database\Eloquent\Model;

class ContractVersionProgram extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'program', 'lessons_planned'
    ];

    protected $casts = [
        'program' => Program::class,
    ];

    public function prices()
    {
        return $this->hasMany(ContractVersionProgramPrice::class);
    }

    public function clientLessons()
    {
        return $this->hasMany(ClientLesson::class);
    }


    public function contractVersion()
    {
        return $this->belongsTo(ContractVersion::class);
    }

    public function clientGroup()
    {
        return $this->hasOne(ClientGroup::class, 'contract_version_program_id', 'id');
    }

    /**
     * Есть ли группа по этой программе договора
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function group()
    {
        return $this->hasOneThrough(
            Group::class,
            ClientGroup::class,
            'contract_version_program_id',
            'id',
            'id',
            'group_id',
        );
    }

    /**
     * Отзанимался по всем занятиям?
     */
    public function getIsClosedAttribute(): bool
    {
        return $this->clientLessons()->count() >= $this->prices()->sum('lessons');
    }

    public function getNextPrice(): int
    {
//        $nextLessonIndex = ClientLesson::query()
//            ->where('contract_id', $this->contractVersion->contract_id)
//            ->count();
//        $nextLessonIndex++;
        $prices = $this->prices;
        return intval($prices[count($prices) - 1][1]); // последняя цена
    }
}
