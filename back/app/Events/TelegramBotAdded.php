<?php

namespace App\Events;

use App\Http\Resources\AuthResource;
use App\Models\Phone;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TelegramBotAdded implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Phone $phone)
    {
    }

    public function broadcastOn(): Channel
    {
        return new Channel('sse');
    }

    public function broadcastWith(): array
    {
        return [
            'data' => new AuthResource($this->phone),
        ];
    }
}
