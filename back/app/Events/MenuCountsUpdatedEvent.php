<?php

namespace App\Events;

use App\Models\Call;
use App\Models\Request as ClientRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MenuCountsUpdatedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array{request?: int, call?: int}  $data
     */
    public function __construct(public array $data) {}

    /**
     * Обновление только счетчика заявок.
     */
    public static function dispatchRequestsCount(): void
    {
        self::dispatch([
            'requests' => ClientRequest::getMenuCount(),
        ]);
    }

    /**
     * Обновление только счетчика звонков.
     */
    public static function dispatchCallsCount(): void
    {
        self::dispatch([
            'calls' => Call::getMenuCount(),
        ]);
    }

    /**
     * Обновление только счетчика звонков.
     */
    public static function dispatchCallsCustom(int $count): void
    {
        self::dispatch([
            'calls' => $count,
        ]);
    }

    /**
     * Обновление только счетчика звонков.
     */
    public static function dispatchRequestsCustom(int $count): void
    {
        self::dispatch([
            'requests' => $count,
        ]);
    }

    public function broadcastOn(): Channel
    {
        return new Channel('sse');
    }

    public function broadcastWith(): array
    {
        return [
            'data' => $this->data,
        ];
    }
}
