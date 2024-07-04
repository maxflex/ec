<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Event extends Model
{
    protected $fillable = [
        'date', 'time', 'name', 'description',
        'is_afterclass', 'duration', 'year'
    ];

    protected $casts = [
        'is_afterclass' => 'boolean'
    ];

    public function participants()
    {
        return $this->hasMany(EventParticipant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTimeEndAttribute()
    {
        if (!$this->duration) {
            return null;
        }

        return Carbon::parse($this->date . ' ' . $this->time)
            ->addMinutes($this->duration)
            ->format("H:i");
    }
}
