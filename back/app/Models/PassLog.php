<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PassLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'entity_id',
        'entity_type',
        'used_at',
        'name',
        'complaint',
    ];

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    public function pass(): BelongsTo
    {
        return $this->belongsTo(Pass::class, 'entity_id');
    }
}
