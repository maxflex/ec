<?php

namespace App\Models;

use App\Enums\TeacherPaymentMethod;
use Illuminate\Database\Eloquent\Model;

class TeacherPayment extends Model
{
    protected $fillable = [
        'purpose', 'date', 'method', 'sum', 'is_verified',
        'teacher_id'
    ];

    protected $casts = [
        'method' => TeacherPaymentMethod::class,
        'is_verified' => 'boolean',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
