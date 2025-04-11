<?php

namespace App\Events;

use App\Http\Resources\ClientTestResource;
use App\Models\ClientTest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClientTestUpdatedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ClientTest $clientTest,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('sse');
    }

    /**
     * @return array{data: mixed}
     */
    public function broadcastWith(): array
    {
        return [
            'data' => new ClientTestResource($this->clientTest),
        ];
    }
}
