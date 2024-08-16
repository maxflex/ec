<?php

namespace App\Models;

use App\Enums\Exam;
use Illuminate\Database\Eloquent\Model;

class ExamDate extends Model
{
    public $timestamps = false;

    protected $fillable = ['dates'];

    protected $casts = [
        'exam' => Exam::class,
        'dates' => 'array'
    ];

    public function getDatesAttribute($value)
    {
        if ($value === null) {
            return [];
        }
        return json_decode($value);
    }
}
