<?php

namespace App\Models;

use App\Enums\TelegramTemplate;
use App\Facades\Telegram;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use TelegramBot\Api\Types\ForceReply;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;
use TelegramBot\Api\Types\ReplyKeyboardRemove;

class TelegramMessage extends Model
{
    protected $fillable = [
        'text', 'list_id', 'template', 'number', 'telegram_id'
    ];

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Отправить сообщение и сохранить TelegramMessage
     *
     * @param InlineKeyboardMarkup|ReplyKeyboardMarkup|ReplyKeyboardRemove|ForceReply|null $replyMarkup
     */
    public static function send(Phone $phone, string|TelegramList $where, $replyMarkup = null)
    {
        if ($where instanceof TelegramList) {
            $listId = $where->id;
            $text = $where->text;
        } else {
            $listId = null;
            $text = $where;
        }
        if ($phone->telegram_id) {
            Telegram::sendMessage(
                $phone->telegram_id,
                $text,
                'HTML',
                replyMarkup: $replyMarkup
            );
        }
        $phone->entity->telegramMessages()->create([
            'list_id' => $listId,
            'telegram_id' => $phone->telegram_id,
            'number' => $phone->number,
            'text' => $text,
        ]);
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
            TelegramMessage::send($phone, $text, $template->getReplyMarkup($callbackData));
        }
    }
}
