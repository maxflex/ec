<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Violation extends Model
{
    protected $fillable = [
        'comment', 'is_resolved', 'lesson_id', 'client_lesson_id', 'file',
    ];

    protected $casts = [
        'file' => 'array',
        'is_resolved' => 'boolean',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function clientLesson(): BelongsTo
    {
        return $this->belongsTo(ClientLesson::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
