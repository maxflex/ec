<?php

namespace App\Models;

use App\Enums\Program;
use Illuminate\Database\Eloquent\Model;

class WebReviewScore extends Model
{
    protected $fillable = ['score', 'max_score', 'program'];

    protected $casts = [
        'program' => Program::class
    ];
}
