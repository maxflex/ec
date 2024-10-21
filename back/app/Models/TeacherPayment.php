<?php

namespace App\Models;

use App\Enums\TeacherPaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherPayment extends Model
{
    protected $fillable = [
        'purpose', 'date', 'method', 'sum',
        'teacher_id', 'year', 'is_confirmed'
    ];

    protected $casts = [
        'method' => TeacherPaymentMethod::class,
        'is_confirmed' => 'bool',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
