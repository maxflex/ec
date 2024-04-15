<?php

namespace App\Http\Controllers\Common;

use App\Events\TelegramBotAdded;
use App\Http\Controllers\Controller;
use App\Models\Phone;
use Illuminate\Http\Request;
use TelegramBot\Api\{Client, Exception};
use TelegramBot\Api\Types\{ReplyKeyboardMarkup, ReplyKeyboardRemove, Update};

class TelegramController extends Controller
{
    public function bot(Request $request)
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
                $chatId = $message->getChat()->getId();
                $bot->sendMessage(
                    $chatId,
                    view('telegram.hello'),
                    "HTML",
                    replyMarkup: $replyMarkup
                );
            });

            //Handle text messages
            $bot->on(function (Update $update) use ($bot) {
                $message = $update->getMessage();
                if ($message === null) {
                    return;
                }
                $chatId = $message->getChat()->getId();
                $contact = $message->getContact();
                if ($contact !== null) {
                    $phone = Phone::auth($contact->getPhoneNumber());
                    if ($phone === null) {
                        $bot->sendMessage($chatId, view('telegram.auth-fail', compact('number')));
                    } else {
                        $phone->telegram_id = $contact->getUserId();
                        $phone->save();
                        TelegramBotAdded::dispatch($phone);
                        $bot->sendMessage(
                            $chatId,
                            view('telegram.auth-success', compact('phone')),
                            'HTML',
                            replyMarkup: new ReplyKeyboardRemove()
                        );
                    }
                } else {
                    // $bot->sendMessage(
                    //     $chatId,
                    //     'Вы написали: ' . $message->getText(),
                    //     replyMarkup: new ReplyKeyboardRemove()
                    // );
                }
                // logger("UPD", json_encode($update->getMe))
                // $id = $message->getChat()->getId();
                // $bot->sendMessage($id, 'Your message: ' . $message->getText());
            }, function () {
                return true;
            });

            $bot->run();
        } catch (Exception $e) {
            $e->getMessage();
        }
    }
}
