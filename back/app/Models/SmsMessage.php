<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsMessage extends Model
{
    const DISABLE_LOGS = true;

    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
