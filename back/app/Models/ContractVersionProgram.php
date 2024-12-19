<?php

namespace App\Models;

use App\Enums\Program;
use App\Traits\RelationSyncable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class ContractVersionProgram extends Model
{
    use RelationSyncable;

    public $timestamps = false;

    protected $fillable = [
        'program', 'lessons_planned'
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

    /**
     * Отзанимался по всем занятиям?
     */
    public function getIsClosedAttribute(): bool
    {
        return $this->clientLessons()->count() >= $this->prices()->sum('lessons');
    }

    /**
     * @param int $plus это временно, для имитации, для NalogController
     * @return int
     */
    public function getNextPrice(int $plus = 0): int
    {
        // сколько занятий уже прошло
        $lessonsPassed = $this->clientLessons()->where('price', '>', 0)->count();
        $lessonsPassed += $plus;

        $cumulativeLessons = 0;
        foreach ($this->prices as $p) {

            // Update cumulative lesson count
            $cumulativeLessons += $p->lessons;

            // Check if lessonsPassed falls within the current block
            if ($lessonsPassed < $cumulativeLessons) {
                return $p->price; // Return price if within block range
            }
        }

        // If no match, return last price by default (fallback case)
        return $this->prices->last()->price;
    }

    public static function booted()
    {
        static::deleting(function (ContractVersionProgram $contractVersionProgram) {
            $contractVersionProgram->prices->each->delete();
        });
    }
}
