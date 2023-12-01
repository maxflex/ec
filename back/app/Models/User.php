<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    // protected $fillable = [
    //     'email',
    //     // 'password',
    // ];
    protected $guarded = [];

    public $timestamps = false;
}
