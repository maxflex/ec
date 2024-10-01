<?php

namespace App\Models;

use App\Enums\EventParticipantConfirmation;
use Illuminate\Database\Eloquent\Model;

class EventParticipant extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'entity_type', 'entity_id', 'confirmation'
    ];

    protected $casts = [
        'confirmation' => EventParticipantConfirmation::class,
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
