<?php

namespace App\Models;

use App\Enums\ContractLessonStatus;
use Illuminate\Database\Eloquent\Model;

class ContractLesson extends Model
{
    protected $casts = [
        'status' => ContractLessonStatus::class
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}
