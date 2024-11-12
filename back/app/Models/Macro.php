<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Macro extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'title', 'text_ooo', 'text_ip'
    ];
}
