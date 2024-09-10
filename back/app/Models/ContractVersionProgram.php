<?php

namespace App\Models;

use App\Enums\Program;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class ContractVersionProgram extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'program', 'lessons_planned', 'prices'
    ];

    protected $casts = [
        'program' => Program::class,
    ];

    public function prices(): HasMany
    {
        return $this->hasMany(ContractVersionProgramPrice::class);
    }

    public function clientLessons(): HasMany
    {
        return $this->hasMany(ClientLesson::class);
    }


    public function contractVersion(): BelongsTo
    {
        return $this->belongsTo(ContractVersion::class);
    }

    public function clientGroup(): HasOne
    {
        return $this->hasOne(ClientGroup::class, 'contract_version_program_id', 'id');
    }

    /**
     * Есть ли группа по этой программе договора
     *
     */
    public function group(): HasOneThrough
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
        return $this->prices()->latest('id')->first()->price;
//        TODO: для того, чтоб это работало, нужна правильная миграция contract_version_program_prices
//        $offset = $this->clientLessons()->count();
//        return $this->prices()->offset($offset)->first()->price;
    }

    public static function booted()
    {
        static::deleting(function (ContractVersionProgram $contractVersionProgram) {
            $contractVersionProgram->prices->each->delete();
        });
    }
}
