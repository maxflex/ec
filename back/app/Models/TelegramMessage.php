<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramMessage extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id', 'text', 'phone_id'
    ];

    public function phone()
    {
        return $this->belongsTo(Phone::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
