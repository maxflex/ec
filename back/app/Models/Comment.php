<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    protected $fillable = [
        'text', 'entity_id', 'entity_type', 'extra',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
