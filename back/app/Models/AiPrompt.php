<?php

namespace App\Models;

use App\Casts\JsonArrayCast;
use Illuminate\Database\Eloquent\Model;

class AiPrompt extends Model
{
    public const int REPORT = 1;

    // Этап 1: audio -> transcript (ASR-процесс).
    public const int CALL_TRANSCRIPTION = 3;

    // Этап 2: transcript -> summary/analysis.
    public const int CALL_ANALYSIS = 4;

    protected $fillable = [
        'title', 'instruction', 'prompt', 'files',
    ];

    protected $casts = [
        'files' => JsonArrayCast::class,
    ];
}
