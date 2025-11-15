<?php

namespace App\Models;

use App\Enums\TeacherPaymentMethod;
use App\Observers\UserIdObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(UserIdObserver::class)]
class TeacherPayment extends Model
{
    protected $fillable = [
        'card_number', 'date', 'method', 'sum',
        'teacher_id', 'year', 'is_confirmed',
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
