<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractVersionPayment extends Model
{
    public $timestamps = false;

    protected $fillable = ['sum', 'date'];

    public function contractVersion(): BelongsTo
    {
        return $this->belongsTo(ContractVersion::class);
    }

    public function getDateObjAttribute(): Carbon
    {
        return Carbon::parse($this->attributes['date']);
    }
}
