<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vacation extends Model
{
    public $timestamps = false;
    protected $fillable = ['date'];
}
