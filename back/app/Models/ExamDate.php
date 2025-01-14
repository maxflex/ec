<?php

namespace App\Models;

use App\Casts\JsonArrayCast;
use App\Enums\Exam;
use App\Enums\Program;
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

    /**
     * @return Program[]
     */
    public function getProgramsAttribute()
    {
        return collect(Program::cases())
            ->filter(fn($p) => $p->getExam() === $this->exam)
            ->all();
    }
}
