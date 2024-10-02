<?php

namespace App\Events;

use App\Models\EventParticipant;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ParticipantConfirmationEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public EventParticipant $eventParticipant)
    {
    }

    public function broadcastOn()
    {
        return new Channel('sse');
    }

    public function broadcastWith()
    {
        return [
            'data' => $this->eventParticipant
        ];
    }
}
