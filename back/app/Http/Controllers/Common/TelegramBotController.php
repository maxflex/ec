<?php

namespace App\Http\Controllers\Common;

use App\Enums\TelegramTemplate;
use App\Events\TelegramBotAdded;
use App\Http\Controllers\Controller;
use App\Models\Phone;
use Illuminate\Http\Request;
use TelegramBot\Api\{Client, Exception};
use TelegramBot\Api\Types\{ReplyKeyboardMarkup, ReplyKeyboardRemove, Update};

class TelegramBotController extends Controller
{
    public function __invoke(Request $request)
    {
        logger("TELEGRAM: " . json_encode($request->all(), JSON_PRETTY_PRINT));
        // dd($request->all());
        try {
            $bot = new Client(config('telegram.key'));

            //Handle /ping command
            $bot->command('ping', function ($message) use ($bot) {
                $bot->sendMessage($message->getChat()->getId(), 'pong!');
            });

            $bot->command('start', function ($message) use ($bot) {
                $buttons = [[
                    ['text' => 'Отправить мой номер телефона', 'request_contact' => true]
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
                    "HTML",
                    replyMarkup: $replyMarkup
                );
            });

            //Handle text messages
            $bot->on(function (Update $update) use ($bot) {
                /**
                 * Обработка шаблонов
                 */
                $callback = $update->getCallbackQuery();
                if ($callback !== null) {
                    $message = $callback->getMessage();
                    $bot->deleteMessage(
                        $message->getChat()->getId(),
                        $message->getMessageId(),
                    );
                    $data = json_decode($callback->getData());
                    $template = TelegramTemplate::from($data->template);
                    $template->callback($data);
                    logger("Callback data:", $data);
                    // logger("answerCallbackQuery");
                    // $bot->answerCallbackQuery(
                    //     $callbackQuery->getId(),
                    //     'Catched the callback query!',
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
                        $phone->telegram_id = $contact->getUserId();
                        $phone->save();
                        TelegramBotAdded::dispatch($phone);
                        $bot->sendMessage(
                            $telegramId,
                            view('bot.auth-success', compact('phone')),
                            'HTML',
                            replyMarkup: new ReplyKeyboardRemove()
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
}
