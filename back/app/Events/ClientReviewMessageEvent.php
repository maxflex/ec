<?php

namespace App\Events;

use App\Http\Resources\ClientReviewListResource;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClientReviewMessageEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly ClientReviewListResource $clientReview,
        public readonly ?string $fakeId = null,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('sse');
    }

    public function broadcastWith(): array
    {
        return [
            'data' => [
                'item' => $this->clientReview,
                'fakeId' => $this->fakeId,
            ],
        ];
    }
}
