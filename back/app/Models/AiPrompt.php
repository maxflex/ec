<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiPrompt extends Model
{
    public const int REPORT = 1;

    // Router для AI-чата по звонкам (text-to-sql + direct_answer).
    public const int CALLS_CHAT = 6;

    // Этап 1: audio -> transcript (ASR-процесс).
    public const int CALL_TRANSCRIPTION = 3;

    // Этап 2: transcript -> summary/analysis.
    public const int CALL_ANALYSIS = 4;

    protected $fillable = [
        'title', 'instruction', 'prompt',
    ];
}
