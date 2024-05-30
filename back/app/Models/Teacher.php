<?php

namespace App\Models;

use App\Enums\TeacherStatus;
use App\Traits\HasPhones;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasPhones;

    protected $casts = [
        'status' => TeacherStatus::class
    ];

    public function payments()
    {
        return $this->hasMany(TeacherPayment::class)->latest();
    }

    public function subjects(): Attribute
    {
        return Attribute::make(
            fn ($value) => $value ? explode(',', $value) : [],
            fn ($value) => $value ? join(',', $value) : null
        );
    }
}
