<?php

namespace App\Console\Commands;

use App\Enums\SendTo;
use App\Enums\TelegramListStatus;
use App\Models\Client;
use App\Models\Teacher;
use App\Models\TelegramList;
use App\Models\TelegramMessage;
use DB;
use Illuminate\Console\Command;

class SendTelegramMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-telegram-messages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // if (is_localhost()) {
        //     DB::table('telegram_messages')->truncate();
        //     DB::table('telegram_lists')
        //         ->where('id', 6)
        //         ->update(['status' => TelegramListStatus::scheduled->value]);
        // }
        $sent = 0;
        $lists = TelegramList::query()
            ->where('status', TelegramListStatus::scheduled)
            ->whereRaw('ifnull(scheduled_at, created_at) <= now()')
            ->get();

        foreach ($lists as $list) {
            $list->update(['status' => TelegramListStatus::sending]);
            $phones = collect();
            $clients = Client::whereIn('id', $list->recipients->clients)->get();
            foreach ($clients as $client) {
                if (in_array(SendTo::students->value, $list->send_to)) {
                    $phones = $phones->merge(
                        $client->phones()->get()
                    );
                }
                if (in_array(SendTo::representatives->value, $list->send_to)) {
                    $phones = $phones->merge(
                        $client->representative->phones()->get()
                    );
                }
            }
            if (in_array(SendTo::teachers->value, $list->send_to)) {
                $teachers = Teacher::whereIn('id', $list->recipients->teachers)->get();
                foreach ($teachers as $teacher) {
                    $phones = $phones->merge(
                        $teacher->phones()->get()
                    );
                }
            }
            foreach ($phones as $phone) {
                $telegramMessage = TelegramMessage::send($phone, $list);
                if ($telegramMessage?->telegram_id) {
                    $sent++;
                }
            }
            $list->update(['status' => TelegramListStatus::sent]);
        }

        $this->info("Sent: $sent");
    }
}
