<?php

namespace App\Models;

use App\Enums\LessonStatus;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $casts = [
        'status' => LessonStatus::class
    ];
}
