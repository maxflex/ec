<?php

namespace App\Models;

use App\Casts\JsonArrayCast;
use App\Enums\Exam;
use Illuminate\Database\Eloquent\Model;

class ExamDate extends Model
{
    public $timestamps = false;

    protected $fillable = ['dates'];

    protected $casts = [
        'exam' => Exam::class,
        'dates' => JsonArrayCast::class
    ];

    protected $appends = ['programs'];

    public function getProgramsAttribute()
    {
        return $this->exam->getPrograms();
    }
}
