<?php

namespace App\Jobs;

use App\Facades\Telegram;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use TelegramBot\Api\Types\Message;

/**
 * Удалят Telegram сообщение "запрос оставить отзыв", если на него не ответили в течение 3 часов
 */
class DeleteClientReviewMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly Message $message) {}

    public function handle(): void
    {
        try {
            Telegram::deleteMessage(
                $this->message->getChat()->getId(),
                $this->message->getMessageId(),
            );
        } catch (\Exception $e) {
        }
    }
}
