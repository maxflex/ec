<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'date', 'time', 'name', 'description',
        'is_afterclass', 'duration'
    ];

    public function participants()
    {
        return $this->hasMany(EventParticipant::class);
    }
}
