<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiPrompt extends Model
{
    public const int REPORT_INSTRUCTION = 1;

    public const int REPORT = 2; // значит, используем этот

    public const int CALL_TRANSCRIPTION = 3;

    protected $fillable = [
        'title', 'instruction', 'prompt',
    ];
}
