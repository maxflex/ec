<?php

namespace App\Models;

use App\Observers\UserIdObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(UserIdObserver::class)]
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
