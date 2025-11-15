<?php

namespace App\Models;

use App\Enums\Direction;
use App\Observers\ContractVersionObserver;
use App\Observers\UserIdObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([ContractVersionObserver::class, UserIdObserver::class])]
class ContractVersion extends Model
{
    protected $fillable = [
        'date', 'sum', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function payments(): HasMany
    {
        return $this->hasMany(ContractVersionPayment::class)->orderBy('date');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Номер по порядку
     */
    public function getSeqAttribute()
    {
        return $this->chain()
            ->where('created_at', '<=', $this->created_at)
            ->count();
    }

    public function chain(): HasMany
    {
        return $this->hasMany(ContractVersion::class, 'contract_id', 'contract_id');
    }

    /**
     * Пред версия
     */
    public function getPrevAttribute(): ?self
    {
        return $this->chain()
            ->where('created_at', '<', $this->created_at)
            ->latest()
            ->first();
    }

    /**
     * Изменение суммы по сравнению с предыдущей версией
     */
    public function getSumChangeAttribute(): int
    {
        if (! $this->prev) {
            return 0;
        }

        return $this->sum - $this->prev->sum;
    }

    /**
     * Обновляем связи contract_version_program_id
     * новая версия – $this, старая $old
     *
     * @return bool успешно/не успешно
     */
    public function relinkIds(ContractVersion $old): bool
    {
        $result = [];
        foreach ($old->programs as $oldProgram) {
            $newProgram = $this->programs->where('program', $oldProgram->program)->first();

            foreach ([ClientGroup::class, ClientLesson::class] as $class) {
                $query = $class::where('contract_version_program_id', $oldProgram->id);
                // есть что обновлять
                if ($query->exists()) {
                    // ... но нет нужной программы в новой версии
                    if (! $newProgram) {
                        return false;
                    }
                    $result[] = [$query, $newProgram->id];
                }
            }
        }

        foreach ($result as $r) {
            [$query, $newProgramId] = $r;
            $query->update(['contract_version_program_id' => $newProgramId]);
        }

        // TODO: проверить
        $old->programs()->update(['status' => null]);
        foreach ($this->programs as $program) {
            $program->updateStatus();
        }

        return true;
    }

    public function programs(): HasMany
    {
        return $this->hasMany(ContractVersionProgram::class);
    }

    public function scopeActive($query)
    {
        $query->where('is_active', true);
    }

    public function setActiveVersion()
    {
        $this->chain()->update(['is_active' => false]);
        $this->chain()->latest()->first()->update(['is_active' => true]);
    }

    /**
     * Направление: кол-во программ
     * Все направления без учета активна / неактивна программа
     *
     * @return array<string, int>
     */
    public function getDirectionCountsAttribute(): array
    {
        $result = [];
        foreach ($this->programs as $program) {
            $direction = $program->program->getDirection()->value;
            if (! isset($result[$direction])) {
                $result[$direction] = 0;
            }
            $result[$direction]++;
        }

        return $result;
    }

    /**
     * Среднее последних цен в программах
     */
    public function getPriceAvgAttribute(): float
    {
        // COMPLETE THE CODE
        // взять все программы, в каждой программе берем самый последний prices->price, и получаем среднее арифметическое
        $result = [];
        foreach ($this->programs as $program) {
            $price = $program->prices->last();
            if ($price) {
                $result[] = $price->price;
            }
        }

        if (count($result) === 0) {
            return 0;
        }

        return round(array_sum($result) / count($result), 2);
    }

    /**
     * Все направления (без учета активна / неактивна программа)
     *
     * @return Direction[]
     */
    public function getDirectionsAttribute(): array
    {
        return $this->programs
            ->map(fn ($p) => $p->program->getDirection())
            ->unique()
            ->all();
    }
}
