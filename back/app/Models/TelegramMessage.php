<?php

namespace App\Models;

use App\Enums\TelegramTemplate;
use App\Facades\Telegram;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use TelegramBot\Api\Types\ForceReply;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;
use TelegramBot\Api\Types\ReplyKeyboardRemove;

class TelegramMessage extends Model
{
    protected $fillable = [
        'text', 'list_id', 'template', 'number', 'telegram_id',
        'user_id',
    ];

    protected $casts = [
        'template' => TelegramTemplate::class,
    ];

    /**
     * @return bool хотя бы одно сообщение доставлено
     */
    public static function sendTemplate(
        TelegramTemplate $template,
        $receiverOrPhone,
        array $viewVariables = [],
        array $callbackData = []
    ): bool {
        $phones = $receiverOrPhone instanceof Phone ? [$receiverOrPhone] : $receiverOrPhone->phones;

        $atLeastOneDelivered = false;

        foreach ($phones as $phone) {
            $text = $template->getText($viewVariables);
            $telegramMessage = TelegramMessage::send($phone, $text, $template->getReplyMarkup($callbackData), $template);
            if ($telegramMessage && $phone->telegram_id && ! $atLeastOneDelivered) {
                $atLeastOneDelivered = true;
            }
        }

        return $atLeastOneDelivered;
    }

    /**
     * Отправить сообщение и сохранить TelegramMessage
     * Неудачные попытки будут отображаться красным в истории сообщений
     *
     * TODO: возвращать null только если сообщение не отправлено (запретить переписку, если telegram_id = null)
     * TODO: после нужно будет обновить код выше $atLeastOneDelivered
     */
    public static function send(
        Phone $phone,
        string|TelegramList $textOrList,
        InlineKeyboardMarkup|ReplyKeyboardMarkup|ReplyKeyboardRemove|ForceReply|null $replyMarkup = null,
        ?TelegramTemplate $template = null,
        ?User $user = null,
    ): ?self {
        if ($phone->is_telegram_disabled) {
            return null;
        }

        if ($textOrList instanceof TelegramList) {
            $listId = $textOrList->id;
            $text = $textOrList->text;
        } else {
            $listId = null;
            $text = $textOrList;
        }

        // если нет telegram_id, всё равно сохраняем попытку отправить
        $telegramMessage = $phone->entity->telegramMessages()->create([
            'list_id' => $listId,
            'telegram_id' => $phone->telegram_id,
            'number' => $phone->number,
            'text' => $text,
            'template' => $template,
            'user_id' => $user?->id,
        ]);

        if ($phone->telegram_id) {
            try {
                $telegramId = is_localhost() ? 84626120 : $phone->telegram_id;
                Telegram::sendMessage(
                    $telegramId,
                    $text,
                    'HTML',
                    replyMarkup: $replyMarkup
                );
            } catch (Exception $e) {
                logger('Cant send telegram message: '.$e->getMessage());
                //                "message": "Bad Request: chat not found",
                //                "exception": "TelegramBot\\Api\\HttpException",
            }

            // когда отправляет пользователь, это не массовое отправление ждать не нужно
            // TODO: улучшить это условие? Когда ещё не нужно ждать?
            if (! $user) {
                sleep(1);
            }
        }

        return $telegramMessage;
    }

    public static function sendNumberChanged(Phone $phone)
    {
        $buttons = [[
            ['text' => 'Отправить мой номер телефона', 'request_contact' => true],
        ]];

        $replyMarkup = new ReplyKeyboardMarkup(
            $buttons,
            oneTimeKeyboard: true,
            resizeKeyboard: true,
            isPersistent: true
        );

        TelegramMessage::send(
            $phone,
            view('bot.number-changed'),
            $replyMarkup,
        );
    }

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }
}
