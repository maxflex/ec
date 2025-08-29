<?php

namespace App\Console\Commands\Once;

use App\Models\Representative;
use App\Models\TelegramMessage;
use App\Utils\MagicLink;
use Illuminate\Console\Command;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class NotifyLkCommand extends Command
{
    protected $signature = 'once:notify-lk';

    protected $description = 'Уведомить родителей, которые давно не заходили в ЛК';

    public function handle(): void
    {
        $representatives = Representative::canLogin()->get();
        $sentTo = collect();
        $host = 'https://lk.ege-centr.ru/login?link=';
        // $host = is_localhost() ? 'http://localhost:3000/login?link=' : 'https://lk.ege-centr.ru/login?link=';

        $bar = $this->output->createProgressBar($representatives->count());
        foreach ($representatives as $representative) {
            if ($representative->last_seen_at !== null) {
                $bar->advance();

                continue;
            }
            foreach ($representative->phones as $phone) {
                $link = MagicLink::create($phone);
                $magicLink = $host.$link;
                // $replyMarkup = new InlineKeyboardMarkup([[[
                //     'text' => 'Перейти в ЛК',
                //     'url' => $magicLink,
                // ]]]);
                TelegramMessage::send($phone, view('once.notify-lk', [
                    'representative' => $representative,
                    'magicLink' => $magicLink,
                ]));
            }
            $sentTo->push($representative->id);
            $bar->advance();
        }
        $bar->finish();
        $this->line(PHP_EOL);
        $this->info($sentTo->join(', '));
    }
}
