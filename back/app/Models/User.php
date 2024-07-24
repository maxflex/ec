<?php

namespace App\Models;

use App\Traits\HasName;
use App\Traits\HasPhones;
use App\Traits\HasPhoto;
use App\Traits\RelationSyncable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasName, HasPhones, HasPhoto, RelationSyncable;

    protected $fillable = [
        'first_name', 'last_name', 'middle_name'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function instructions()
    {
        return $this->hasMany(Instruction::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function requests()
    {
        return $this->hasMany(Request::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function telegramMessages()
    {
        return $this->hasMany(TelegramMessage::class);
    }

    public function examScores()
    {
        return $this->hasMany(ExamScore::class);
    }

    public function contractVersions()
    {
        return $this->hasMany(ContractVersion::class);
    }

    public function clientPayments()
    {
        return $this->hasMany(ClientPayment::class);
    }

    public function contractPayments()
    {
        return $this->hasMany(ContractPayment::class);
    }

    public function teacherPayments()
    {
        return $this->hasMany(TeacherPayment::class);
    }

    public function teacherServices()
    {
        return $this->hasMany(TeacherService::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }
}
