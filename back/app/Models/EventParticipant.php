<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventParticipant extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'entity_type', 'entity_id', 'is_confirmed'
    ];

    protected $casts = [
        'is_confirmed' => 'boolean'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function entity()
    {
        return $this->morphTo('entity');
    }
}
