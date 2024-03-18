<?php

namespace App\Console\Commands;

use App\Facades\Telegram;
use Illuminate\Console\Command;

class TelegramCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:telegram';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // $tg = new \TelegramBot\Api\Client(config('telegram.key'));
        $tg = new \TelegramBot\Api\BotApi(config('telegram.key'));
        dd(
            $tg->sendMessage(84626120, 'test'),
            // $tg->getChatMemberCount(5637080396)
            // $tg->getMe()
        );
    }
}
