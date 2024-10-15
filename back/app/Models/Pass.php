<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Pass extends Model
{
    protected $fillable = [
        'comment', 'type', 'date', 'request_id'
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(Request::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function passLog(): MorphOne
    {
        return $this->morphOne(PassLog::class, 'entity');
    }

    public function getUsedAtAttribute(): ?string
    {
        return $this->passLog?->used_at;
    }
}
