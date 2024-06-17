<?php

namespace App\Models;

use App\Traits\RelationSyncable;
use Illuminate\Database\Eloquent\Model;

class ContractVersion extends Model
{
    use RelationSyncable;

    protected $fillable = [
        'contract_id', 'date', 'sum'
    ];

    public function programs()
    {
        return $this->hasMany(ContractProgram::class)->orderBy('is_closed');
    }

    public function payments()
    {
        return $this->hasMany(ContractPayment::class)->orderBy('date');
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
     * Предыдущая версия договора
     * учитываем, что версии могут идти так: 1, 3, 4 (удалена в середине)
     */
    public function getPreviousAttribute()
    {
        return $this->chain()
            ->where('version', '<', $this->version)
            ->orderBy('version', 'desc')
            ->first();
    }

    public function scopeLastVersions($query)
    {
        $sub = self::selectRaw(<<<SQL
            contract_id, MAX(version) as max_version
        SQL)->groupBy('contract_id');

        $query->joinSub(
            $sub,
            'last_versions',
            fn ($join) =>
            $join
                ->on('contract_versions.contract_id', '=', 'last_versions.contract_id')
                ->on('contract_versions.version', '=', 'last_versions.max_version')
        );
    }

    public static function booted()
    {
        static::creating(function ($contractVersion) {
            $contractVersion->version = $contractVersion->chain()->max('version') + 1;
        });
    }
}
