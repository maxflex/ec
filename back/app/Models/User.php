<?php

namespace App\Models;

use App\Traits\HasPhones;
use App\Traits\HasPhoto;
use App\Traits\RelationSyncable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasPhones, HasPhoto, RelationSyncable;

    protected $fillable = [
        'first_name', 'last_name', 'middle_name'
    ];

    protected $casts = [
        'is_active' => 'boolean'
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

    public function teacherServices()
    {
        return $this->hasMany(TeacherService::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }
}
