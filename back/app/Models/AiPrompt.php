<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiPrompt extends Model
{
    public const int REPORT = 1;

    // Этап 1 (новый формат): audio -> transcript (ASR-процесс), запись в стерео.
    public const int CALL_TRANSCRIPTION_STEREO = 3;

    // Этап 1 (legacy): audio -> transcript (ASR-процесс), запись в моно.
    public const int CALL_TRANSCRIPTION_MONO = 6;

    // Этап 2: transcript -> summary/analysis.
    public const int CALL_ANALYSIS = 4;

    protected $fillable = [
        'title', 'instruction', 'prompt',
    ];
}
