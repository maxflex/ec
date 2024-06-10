<?php

namespace App\Models;

use App\Traits\HasPhones;
use App\Traits\RelationSyncable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasPhones, RelationSyncable;

    protected $fillable = [
        'first_name', 'last_name', 'middle_name'
    ];

    public function requests()
    {
        return $this->hasMany(Request::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function contractVersions()
    {
        return $this->hasMany(ContractVersion::class);
    }

    public function clientPayments()
    {
        return $this->hasMany(ClientPayment::class);
    }

    public function teacherPayments()
    {
        return $this->hasMany(TeacherPayment::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }
}
