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

    public static function confirm($data)
    {
        $phone = Phone::find($data->phone_id);
        $query = EventParticipant::where('event_id', $data->event_id);

        if ($phone->entity_type === ClientParent::class) {
            $query->where([
                ['entity_type', Client::class],
                ['entity_id', $phone->entity->client->id],
            ]);
        } else {
            $query->where([
                ['entity_type', $phone->entity_type],
                ['entity_id', $phone->entity_id],
            ]);
        }

        $participant = $query->first();
        $participant->update([
            'confirmation' => EventParticipantConfirmation::from($data->confirmation)
        ]);
    }
}
