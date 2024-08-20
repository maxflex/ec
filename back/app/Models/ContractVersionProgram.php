<?php

namespace App\Models;

use App\Enums\Program;
use Illuminate\Database\Eloquent\Model;

class ContractVersionProgram extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'program', 'lessons_planned', 'prices'
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

    public function setPricesAttribute($prices)
    {
        $actualIds = array_column($prices, 'id');
        $this->prices()->whereNotIn('id', $actualIds)->each(fn($model) => $model->delete());
        foreach ($prices as $p) {
            if (isset($p['id']) && $p['id'] > 0) {
                $this->prices()->find($p['id'])->update($p);
            } else {
                $this->prices()->create($p);
            }
        }
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
        $offset = $this->clientLessons()->count();
        return $this->prices()->offset($offset)->first()->price;
    }

    public static function booted()
    {
        static::deleting(function (ContractVersionProgram $contractVersionProgram) {
            $contractVersionProgram->prices->each->delete();
        });
    }
}
