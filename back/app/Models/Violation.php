<?php

namespace App\Models;

use App\Traits\HasComments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Violation extends Model
{
    use HasComments;

    protected $fillable = [
        'is_resolved', 'lesson_id', 'client_lesson_id',
        'photo', 'video',
    ];

    protected $casts = [
        'photo' => 'array',
        'video' => 'array',
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
