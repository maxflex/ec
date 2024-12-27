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

    protected $casts = [
        'template' => TelegramTemplate::class
    ];

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Отправить сообщение и сохранить TelegramMessage
     * Неудачные попытки будут отображаться красным в истории сообщений
     *
     * @param InlineKeyboardMarkup|ReplyKeyboardMarkup|ReplyKeyboardRemove|ForceReply|null $replyMarkup
     */
    public static function send(
        Phone               $phone,
        string|TelegramList $textOrList,
                            $replyMarkup = null,
        ?TelegramTemplate   $template = null,
    ): bool
    {
        if ($textOrList instanceof TelegramList) {
            $listId = $textOrList->id;
            $text = $textOrList->text;
        } else {
            $listId = null;
            $text = $textOrList;
        }

        // если нет telegram_id, всё равно сохраняем попытку отправить
        $phone->entity->telegramMessages()->create([
            'list_id' => $listId,
            'telegram_id' => $phone->telegram_id,
            'number' => $phone->number,
            'text' => $text,
            'template' => $template,
        ]);

        if ($phone->telegram_id) {
            try {
                Telegram::sendMessage(
                    $phone->telegram_id,
                    $text,
                    'HTML',
                    replyMarkup: $replyMarkup
                );
            } catch (\Exception $e) {
                logger("Cant send telegram message", $phone->toArray());
//                "message": "Bad Request: chat not found",
//                "exception": "TelegramBot\\Api\\HttpException",
            }
            return true;
        }
        return false;
    }

    public static function sendTemplate(
        TelegramTemplate $template,
        $receiver,
        array $viewVariables = [],
        array $callbackData = []
    ) {
        foreach ($receiver->phones as $phone) {
            $text = $template->getText($viewVariables);
            TelegramMessage::send($phone, $text, $template->getReplyMarkup($callbackData), $template);
        }
    }
}
