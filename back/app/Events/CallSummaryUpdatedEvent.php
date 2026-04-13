<?php

namespace App\Events;

use App\Models\Call;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallSummaryUpdatedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Call $call,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('sse');
    }

    /**
     * @return array{
     *     data: array{
     *         id: int,
     *         summary: string,
     *         user_id: int|null
     *     }
     * }
     */
    public function broadcastWith(): array
    {
        return [
            'data' => [
                'id' => $this->call->id,
                'summary' => (string) $this->call->summary,
                'user_id' => $this->call->user_id,
            ],
        ];
    }
}
