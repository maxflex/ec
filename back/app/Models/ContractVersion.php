<?php

namespace App\Models;

use App\Traits\RelationSyncable;
use Illuminate\Database\Eloquent\Model;

class ContractVersion extends Model
{
    use RelationSyncable;

    protected $fillable = [
        'contract_id', 'date', 'sum',
        // TODO: remove (is_active should not be fillable)
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function programs()
    {
        return $this->hasMany(ContractVersionProgram::class);
    }

    public function payments()
    {
        return $this->hasMany(ContractVersionPayment::class)->orderBy('date');
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function chain()
    {
        return $this->hasMany(ContractVersion::class, 'contract_id', 'contract_id');
    }

    public function user()
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
     * Создать архив на основе активной версии
     */
    public function createArchive(): self
    {
        // Создаём архив на основе активной версии
        $archiveCv = $this->replicate();
        $archiveCv->is_active = false;
        $archiveCv->setCreatedAt($this->created_at);
        $archiveCv->save();

        // Clone the `programs` relationship with new IDs
        foreach ($this->programs as $program) {
            $clonedProgram = $program->replicate();
            $clonedProgram->contract_version_id = $archiveCv->id;
            $clonedProgram->save();
            foreach ($program->prices as $price) {
                $clonedPrice = $price->replicate();
                $clonedPrice->contract_version_program_id = $clonedProgram->id;
                $clonedPrice->save();
            }
        }

        // Clone the `payments` relationship with new IDs
        foreach ($this->payments as $payment) {
            $clonedPayment = $payment->replicate();
            $clonedPayment->contract_version_id = $archiveCv->id;
            $clonedPayment->save();
        }

        return $archiveCv;
    }

    /**
     * Номер версии
     */
    public function getVersionAttribute()
    {
        return $this->chain()->where('created_at', '<=', $this->created_at)->count();
    }

    public function scopeActive($query)
    {
        $query->where('is_active', true);
    }
}
