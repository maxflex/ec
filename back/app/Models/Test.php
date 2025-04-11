<?php

namespace App\Models;

use App\Casts\JsonArrayCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Test extends Model
{
    protected $fillable = [
        'name', 'minutes', 'questions', 'file', 'description',
    ];

    protected $casts = [
        'questions' => JsonArrayCast::class,
        'file' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getMaxScoreAttribute(): int
    {
        $result = 0;
        foreach ($this->questions as $question) {
            $result += $question->score * count($question->answers);
        }

        return $result;
    }
}
