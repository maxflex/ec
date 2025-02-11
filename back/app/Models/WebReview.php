<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WebReview extends Model
{
    protected $fillable = [
        'text', 'signature', 'rating', 'client_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function examScores(): BelongsToMany
    {
        return $this->belongsToMany(ExamScore::class);
    }
}
