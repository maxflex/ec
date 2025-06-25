<?php

namespace App\Models;

use App\Enums\LessonStatus;
use App\Enums\Program;
use App\Enums\SwampStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Collection;

class ContractVersionProgram extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'program', 'lessons_planned',
    ];

    protected $casts = [
        'program' => Program::class,
        'status' => SwampStatus::class,
    ];

    public static function booted()
    {
        static::deleting(function (ContractVersionProgram $contractVersionProgram) {
            $contractVersionProgram->prices->each->delete();
        });
    }

    /**
     * Программа активна
     */
    public function getIsActiveAttribute(): bool
    {
        return in_array($this->status, SwampStatus::getActiveStatuses(), true);
    }

    public function contractVersion(): BelongsTo
    {
        return $this->belongsTo(ContractVersion::class);
    }

    /**
     * Есть ли группа по этой программе договора
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
     * TODO: снести?
     */
    public function getIsClosedAttribute(): bool
    {
        return $this->clientLessons()->count() >= $this->prices()->sum('lessons');
    }

    public function clientLessons(): HasMany
    {
        return $this->hasMany(ClientLesson::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ContractVersionProgramPrice::class);
    }

    /**
     * Сколько занятий по программе прошло
     * бесплатные и price=0 не учитываем
     */
    public function getLessonsConductedAttribute(): int
    {
        return $this->clientLessons()
            ->where('price', '>', 0)
            ->count();
    }

    /**
     * Сколько занятий по программе прошло, но ещё препод не отметил
     * (сколько занятий в статусе planned сегодня и ранее в группе,
     * в которой он находится сейчас по текущей программе)
     */
    public function getLessonsToBeConductedAttribute(): int
    {
        $group = $this->group;

        if (! $group) {
            return 0;
        }

        return $group->lessons()
            ->where('status', LessonStatus::planned)
            ->where('is_free', false)
            ->where('date', '<=', now()->format('Y-m-d'))
            ->count();
    }

    /**
     * @return Collection<int, int>
     */
    public function getClientLessonPricesAttribute()
    {
        return $this->clientLessons()
            ->join('lessons as l', 'l.id', '=', 'client_lessons.lesson_id')
            ->where('client_lessons.price', '>', 0)
            ->orderByRaw('l.date asc, l.time asc')
            ->pluck('client_lessons.price');
    }

    public function getLessonsTotalAttribute(): int
    {
        $total = $this->lessons_conducted;
        $group = $this->group;

        if (! $group) {
            return $total;
        }

        $groupLessons = $group->lessons_planned - $group->lessons()
            ->where('status', LessonStatus::conducted)
            ->where('is_free', false)
            ->count();

        return $total + $groupLessons;
    }

    public function getNextPrice(?int $lessonsPassed = null): int
    {
        if ($lessonsPassed === null) {
            // сколько занятий уже прошло
            $lessonsPassed = $this->clientLessons()->where('price', '>', 0)->count();
        }

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

    public function getTotalPricePassedAttribute(): int
    {
        return $this->clientLessons()->sum('price');
    }

    public function getTotalPriceAttribute(): int
    {
        return $this->prices->sum(fn (ContractVersionProgramPrice $p) => $p->lessons * $p->price);
    }

    public function getTotalLessonsAttribute(): int
    {
        return $this->prices->sum('lessons');
    }

    public function updateStatus(): bool
    {
        $totalPricePassed = $this->total_price_passed;
        $totalPrice = $this->total_price;
        $hasGroup = $this->clientGroup()->exists();

        $this->status = match (true) {
            $hasGroup && $totalPricePassed < $totalPrice => SwampStatus::inProcess,
            $hasGroup && $totalPricePassed > $totalPrice => SwampStatus::exceedInGroup,
            $hasGroup && $totalPricePassed === $totalPrice => SwampStatus::finishedInGroup,
            // ниже будет только !$hasGroup
            $totalPricePassed < $totalPrice => SwampStatus::toFulfil,
            $totalPricePassed > $totalPrice => SwampStatus::exceedNoGroup,
            $totalPricePassed === $totalPrice => SwampStatus::finishedNoGroup,
        };

        return $this->save();
    }

    public function clientGroup(): HasOne
    {
        return $this->hasOne(ClientGroup::class, 'contract_version_program_id', 'id');
    }
}
