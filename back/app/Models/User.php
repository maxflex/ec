<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
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

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }
}
