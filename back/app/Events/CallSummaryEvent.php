<?php

namespace App\Events;

use App\Http\Resources\CallListResource;
use App\Models\Call;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallSummaryEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Call $call)
    {
    }

    public function broadcastOn()
    {
        return new Channel('sse');
    }

    public function broadcastWith()
    {
        return [
            'data' => new CallListResource($this->call)
        ];
    }
}
