<?php

namespace App\Traits;

use App\Models\TelegramMessage;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasTelegramMessages
{
    public function telegramMessages(): MorphMany
    {
        return $this->morphMany(TelegramMessage::class, 'entity');
    }
}
