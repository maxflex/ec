<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WebReview extends Model
{
    protected $fillable = [
        'text', 'signature', 'rating', 'is_published', 'client_id'
    ];

    protected $casts = [
        'is_published' => 'bool'
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
