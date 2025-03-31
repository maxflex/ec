<?php

namespace App\Console\Commands;

use App\Enums\EventParticipantConfirmation;
use App\Enums\SendTo;
use App\Enums\TelegramListStatus;
use App\Models\Client;
use App\Models\Teacher;
use App\Models\TelegramList;
use App\Models\TelegramMessage;
use DB;
use Illuminate\Console\Command;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\ReplyKeyboardRemove;

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
        if (is_localhost()) {
            DB::table('telegram_messages')->truncate();
            DB::table('telegram_lists')
                ->where('id', 6)
                ->update(['status' => TelegramListStatus::scheduled->value]);
        }
        $sent = 0;
        $lists = TelegramList::query()
            ->where('status', TelegramListStatus::scheduled)
            ->whereRaw('ifnull(scheduled_at, created_at) <= now()')
            ->get();

        foreach ($lists as $list) {
            $list->update(['status' => TelegramListStatus::sending]);
            $toSend = collect();
            $clients = Client::whereIn('id', $list->recipients->clients)->get();
            foreach ($clients as $client) {
                if (in_array(SendTo::students->value, $list->send_to)) {
                    $toSend = $toSend->merge(
                        $client->phones()->get()
                    );
                }
                if (in_array(SendTo::parents->value, $list->send_to)) {
                    $toSend = $toSend->merge(
                        $client->parent->phones()->get()
                    );
                }
            }
            if (in_array(SendTo::teachers->value, $list->send_to)) {
                $teachers = Teacher::whereIn('id', $list->recipients->teachers)->get();
                foreach ($teachers as $teacher) {
                    $toSend = $toSend->merge(
                        $teacher->phones()->get()
                    );
                }
            }
            foreach ($toSend as $phone) {
                if ($list->event_id && $list->is_confirmable) {
                    $replyMarkup = new InlineKeyboardMarkup([
                        [[
                            'text' => '✅ подтвердить участие',
                            'callback_data' => json_encode([
                                'event_id' => $list->event_id,
                                'phone_id' => $phone->id,
                                'confirmation' => EventParticipantConfirmation::confirmed->value,
                            ]),
                        ]], [[
                            'text' => 'отказаться',
                            'callback_data' => json_encode([
                                'event_id' => $list->event_id,
                                'phone_id' => $phone->id,
                                'confirmation' => EventParticipantConfirmation::rejected->value,
                            ]),
                        ]]]);
                } else {
                    $replyMarkup = new ReplyKeyboardRemove;
                }
                $telegramMessage = TelegramMessage::send($phone, $list, $replyMarkup);
                if ($telegramMessage?->telegram_id) {
                    $sent++;
                }
            }
            $list->update(['status' => TelegramListStatus::sent]);
        }

        $this->info("Sent: $sent");
    }
}
