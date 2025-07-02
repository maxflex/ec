<?php

namespace App\Console\Commands\Notification;

use App\Models\Pass;
use App\Models\Request;
use App\Utils\Sms;
use Illuminate\Console\Command;

class PassNotificationCommand extends Command
{
    protected $signature = 'notification:pass-notification';

    protected $description = 'Напоминание о запланированной встрече (неиспользованный пропуск на завтра)';

    public function handle(): void
    {
        $message = 'Здравствуйте. Напоминаем, что завтра Вы записаны на встречу в ЕГЭ-Центр. С уважением, учебная часть.';

        $requestIds = Pass::query()
            ->where('date', now()->addDay()->format('Y-m-d'))
            ->whereNotNull('request_id')
            ->distinct('request_id')
            ->pluck('request_id');

        $requests = Request::whereIn('id', $requestIds)->get();
        $bar = $this->output->createProgressBar($requests->count());

        foreach ($requests as $request) {
            foreach ($request->phones as $phone) {
                Sms::send($phone, $message);
            }
            $bar->advance();
        }
        $bar->finish();
    }
}
