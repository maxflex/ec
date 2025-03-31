<?php

namespace App\Models;

use App\Casts\JsonArrayCast;
use App\Enums\Program;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Test extends Model
{
    protected $fillable = [
        'name', 'minutes', 'program', 'questions', 'file',
    ];

    protected $casts = [
        'program' => Program::class,
        'questions' => JsonArrayCast::class,
        'file' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
