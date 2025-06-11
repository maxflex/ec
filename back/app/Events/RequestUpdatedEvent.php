<?php

namespace App\Events;

use App\Models\Request as ClientRequest;
use App\Http\Resources\RequestListResource;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RequestUpdatedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ClientRequest $request)
    {
    }

    public function broadcastOn()
    {
        return new Channel('sse');
    }

    public function broadcastWith()
    {
        return [
            'data' => new RequestListResource($this->request),
        ];
    }
}
