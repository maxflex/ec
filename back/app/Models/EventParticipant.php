<?php

namespace App\Models;

use App\Enums\EventParticipantConfirmation;
use Illuminate\Database\Eloquent\Model;

class EventParticipant extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'entity_type', 'entity_id', 'confirmation', 'is_visited',
    ];

    protected $casts = [
        'confirmation' => EventParticipantConfirmation::class,
        'is_visited' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function entity()
    {
        return $this->morphTo('entity');
    }

    public function getIsMe(Client|Teacher $model): bool
    {
        return $this->entity_id === $model->id
            && $this->entity_type === get_class($model);
    }
}
