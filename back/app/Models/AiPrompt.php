<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiPrompt extends Model
{
    public const int REPORT = 1;

    public const int CALL = 3;

    protected $fillable = [
        'title', 'instruction', 'prompt',
    ];
}
