<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Phone;
use Illuminate\Http\Request;
use TelegramBot\Api\{Client, Exception};
use TelegramBot\Api\Types\{ReplyKeyboardMarkup, Update};

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
                $replyMarkup = new ReplyKeyboardMarkup($buttons, true, false, true);
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
                $chatId = $message->getChat()->getId();
                if ($message->getContact() != null) {
                    $number = $message->getContact()->getPhoneNumber();
                    $phone = Phone::auth($number);
                    if ($phone === null) {
                        $bot->sendMessage($chatId, view('telegram.auth-fail', compact('number')));
                    } else {
                        $bot->sendMessage($chatId, view('telegram.auth-success', compact('phone')));
                    }
                } else {
                    $bot->sendMessage($chatId, 'Вы написали: ' . $message->getText());
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
