<?php

namespace App\Models;

use App\Enums\TelegramTemplate;
use App\Facades\Telegram;
use Illuminate\Database\Eloquent\Model;

class TelegramMessage extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id', 'text', 'phone_id', 'entry_id', 'template'
    ];

    public function phone()
    {
        return $this->belongsTo(Phone::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param Phone[] $phones
     */
    public static function sendTemplate(
        TelegramTemplate $template,
        array $phones,
        array $viewVariables = [],
        array $callbackData = []
    ) {
        foreach ($phones as $phone) {
            $text = $template->getText($viewVariables);
            $message = Telegram::sendMessage(
                $phone->telegram_id,
                $text,
                'HTML',
                replyMarkup: $template->getReplyMarkup($callbackData)
            );
            $phone->telegramMessages()->create([
                'id' => $message->getMessageId(),
                'text' => $text,
                'template' => $template->value
            ]);
        }
    }
}
