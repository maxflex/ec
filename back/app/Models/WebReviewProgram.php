<?php

namespace App\Models;

use App\Enums\Program;
use Illuminate\Database\Eloquent\Model;

class WebReviewProgram extends Model
{
    public $timestamps = false;

    protected $fillable = ['program'];

    protected $casts = [
        'program' => Program::class,
    ];
}
