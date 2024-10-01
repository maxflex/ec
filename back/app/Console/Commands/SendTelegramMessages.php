<?php

namespace App\Console\Commands;

use App\Enums\SendTo;
use App\Models\{Client, Teacher, TelegramList};
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
        $sent = 0;
        $lists = TelegramList::query()
            ->where('is_sent', false)
            ->whereRaw("ifnull(scheduled_at, created_at) <= now()")
            ->get();

        foreach ($lists as $list) {
            $toSend = collect();
            $clients = Client::whereIn('id', $list->recipients->clients)->get();
            $teachers = Teacher::whereIn('id', $list->recipients->teachers)->get();
            foreach ($clients as $client) {
                if ($list->send_to === SendTo::students || $list->send_to === SendTo::studentsAndParents) {
                    $toSend = $toSend->merge(
                        $client->phones()->withTelegram()->get()
                    );
                }
                if ($list->send_to === SendTo::parents || $list->send_to === SendTo::studentsAndParents) {
                    $toSend = $toSend->merge(
                        $client->parent->phones()->withTelegram()->get()
                    );
                }
            }
            foreach ($teachers as $teacher) {
                $toSend = $toSend->merge(
                    $teacher->phones()->withTelegram()->get()
                );
            }
            foreach ($toSend as $phone) {
                $phone->sendTelegram($list);
                $sent++;
                sleep(1);
            }
            $list->is_sent = true;
            $list->save();
        }

        $this->info("Sent: $sent");
    }

}
