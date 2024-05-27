<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    public function requests()
    {
        return $this->hasMany(Request::class);
    }
}
