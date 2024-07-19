<?php

namespace App\Traits;

use App\Models\TelegramMessage;

trait HasTelegramMessages
{
    public function telegramMessages()
    {
        return $this->morphMany(TelegramMessage::class, 'entity');
    }
}
