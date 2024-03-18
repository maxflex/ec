<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TelegramController extends Controller
{
    public function bot()
    {
        try {
            $bot = new \TelegramBot\Api\Client(config('telegram.key'));

            //Handle /ping command
            $bot->command('ping', function ($message) use ($bot) {
                $bot->sendMessage($message->getChat()->getId(), 'pong!');
            });

            //Handle text messages
            $bot->on(function (\TelegramBot\Api\Types\Update $update) use ($bot) {
                $message = $update->getMessage();
                $id = $message->getChat()->getId();
                $bot->sendMessage($id, 'Your message: ' . $message->getText());
            }, function () {
                return true;
            });

            $bot->run();
        } catch (\TelegramBot\Api\Exception $e) {
            $e->getMessage();
        }
    }
}
