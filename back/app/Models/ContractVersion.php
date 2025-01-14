<?php

namespace App\Models;

use App\Enums\Direction;
use App\Observers\ContractVersionObserver;
use App\Traits\RelationSyncable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(ContractVersionObserver::class)]
class ContractVersion extends Model
{
    use RelationSyncable;

    protected $fillable = [
        'contract_id', 'date', 'sum', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function programs(): HasMany
    {
        return $this->hasMany(ContractVersionProgram::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ContractVersionPayment::class)->orderBy('date');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function chain(): HasMany
    {
        return $this->hasMany(ContractVersion::class, 'contract_id', 'contract_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Последние версии в цепи
     * TODO: remove @DEPRICATED
     */
    public function scopeLastVersions($query)
    {
        $sub = self::selectRaw(<<<SQL
            contract_id as max_contract_id,
            MAX(version) as max_version
        SQL)->groupBy('contract_id');

        $query->joinSub(
            $sub,
            'last_versions',
            fn ($join) => $join
                ->on('contract_versions.contract_id', '=', 'last_versions.max_contract_id')
                ->on('contract_versions.version', '=', 'last_versions.max_version')
        );
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
     * Обновляем связи contract_version_program_id
     * новая версия – $this, старая $old
     *
     * @return bool успешно/не успешно
     */
    public function relinkIds(ContractVersion $old): bool
    {
        $result = [];
        foreach ($old->programs as $program) {
            $newProgram = $this->programs->where('program', $program->program)->first();

            foreach ([ClientGroup::class, ClientLesson::class] as $class) {
                $query = $class::where('contract_version_program_id', $program->id);
                // есть что обновлять
                if ($query->exists()) {
                    // ... но нет нужной программы в новой версии
                    if (!$newProgram) {
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

        return true;
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
     * @return Direction[]
     */
    public function getDirectionsAttribute(): array
    {
        $directions = [];

        // Проходим по каждой программе и определяем ее направление
        foreach ($this->programs as $p) {
            $direction = $p->program->getDirection();

            if (!in_array($direction, $directions)) {
                $directions[] = $direction;
            }
        }

        return $directions;
    }
}
