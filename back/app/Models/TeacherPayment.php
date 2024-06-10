<?php

namespace App\Models;

use App\Enums\TeacherPaymentMethod;
use Illuminate\Database\Eloquent\Model;

class TeacherPayment extends Model
{
    protected $fillable = [
        'purpose', 'date', 'method', 'sum', 'teacher_id', 'year'
    ];

    protected $casts = [
        'method' => TeacherPaymentMethod::class,
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
