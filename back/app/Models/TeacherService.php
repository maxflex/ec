<?php

namespace App\Models;

use App\Observers\UserIdObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(UserIdObserver::class)]
class TeacherService extends Model
{
    protected $fillable = [
        'sum', 'date', 'year', 'purpose', 'teacher_id',
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
