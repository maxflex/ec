<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AppUpdatedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param array<string> $resetFiltersKeys ключи для очистки фильтров на фронте
     */
    public function __construct(public array $resetFiltersKeys = [])
    {
    }

    public function broadcastOn()
    {
        return new Channel('sse');
    }

    public function broadcastWith()
    {
        return [
            'data' => $this->resetFiltersKeys
        ];
    }
}
