<?php

namespace App\Http\Controllers\Common;

use App\Enums\TelegramTemplate;
use App\Events\TelegramBotAdded;
use App\Http\Controllers\Controller;
use App\Models\EventParticipant;
use App\Models\Phone;
use Illuminate\Http\Request;
use TelegramBot\Api\Client;
use TelegramBot\Api\Exception;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;
use TelegramBot\Api\Types\ReplyKeyboardRemove;
use TelegramBot\Api\Types\Update;

class TelegramBotController extends Controller
{
    public function __invoke(Request $request)
    {
        if (is_localhost()) {
            logger('TELEGRAM: '.json_encode($request->all(), JSON_PRETTY_PRINT));
        }

        if ($request->has('my_chat_member')) {
            $botDeleted = $this->onBotDeleted($request);
            if ($botDeleted) {
                return;
            }
        }

        try {
            $bot = new Client(config('telegram.key'));

            // Handle /ping command
            $bot->command('ping', function ($message) use ($bot) {
                $bot->sendMessage($message->getChat()->getId(), 'pong!');
            });

            $bot->command('start', function ($message) use ($bot) {
                $buttons = [[
                    ['text' => 'Отправить мой номер телефона', 'request_contact' => true],
                ]];
                $replyMarkup = new ReplyKeyboardMarkup(
                    $buttons,
                    oneTimeKeyboard: true,
                    resizeKeyboard: true,
                    isPersistent: true
                );
                $telegramId = $message->getChat()->getId();
                $bot->sendMessage(
                    $telegramId,
                    view('bot.hello'),
                    'HTML',
                    replyMarkup: $replyMarkup
                );
            });

            // Handle text messages
            $bot->on(function (Update $update) use ($bot) {

                $callback = $update->getCallbackQuery();
                /**
                 * Обработка кнопок
                 */
                if ($callback !== null) {
                    $message = $callback->getMessage();
                    $chatId = $message->getChat()->getId();
                    $data = json_decode($callback->getData());
                    if (is_localhost()) {
                        logger('Callback data:', (array) $data);
                    }

                    /**
                     * Обработка шаблонов
                     */
                    if (isset($data->template)) {
                        $bot->deleteMessage(
                            $chatId,
                            $message->getMessageId(),
                        );

                        $template = TelegramTemplate::from($data->template);
                        $template->callback($data, (int) $chatId);

                        return;
                    }
                    /**
                     * Подтверждение событий
                     */
                    if (isset($data->event_id)) {
                        EventParticipant::confirm($data, $bot, $callback);

                        return;
                    }

                    // logger("answerCallbackQuery");
                    // $bot->answerCallbackQuery(
                    //     $callbackQuery->getId(),
                    //     'Caught the callback query!',
                    //     true,
                    // );
                    return;
                }

                $message = $update->getMessage();
                if ($message === null) {
                    return;
                }
                $telegramId = $message->getChat()->getId();
                $contact = $message->getContact();

                /**
                 * Поделиться номером телефона
                 */
                if ($contact !== null) {
                    $number = $contact->getPhoneNumber();
                    $phone = Phone::auth($number);
                    if ($phone === null) {
                        $bot->sendMessage($telegramId, view('bot.auth-fail', compact('number')));
                    } else {
                        $phone->update([
                            'telegram_id' => $contact->getUserId(),
                        ]);
                        TelegramBotAdded::dispatch($phone);
                        $bot->sendMessage(
                            $telegramId,
                            view('bot.auth-success', compact('phone')),
                            'HTML',
                            replyMarkup: new ReplyKeyboardRemove
                        );
                    }

                    return;
                }

                /**
                 * Произвольное сообщение клиента – в администрацию
                 * UPD. Отключено
                 */
            }, function () {
                return true;
            });

            $bot->run();
        } catch (Exception $e) {
            $e->getMessage();
        }
    }

    /**
     * Бота удалили
     * TODO: переделать, когда выйдет v3
     * https://github.com/TelegramBot/Api/blob/master/CHANGELOG.md#300---yyyy-mm-dd
     */
    private function onBotDeleted(Request $request): bool
    {
        $myChatMember = $request->input('my_chat_member');
        if (isset($myChatMember['new_chat_member']) && $myChatMember['new_chat_member']['status'] === 'kicked') {
            $telegramId = $myChatMember['chat']['id'];
            Phone::where('telegram_id', $telegramId)->first()?->update([
                'telegram_id' => null,
            ]);

            return true;
        }

        return false;
    }
}
