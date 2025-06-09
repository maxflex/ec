<?php

namespace App\Console\Commands;

use App\Models\SmsMessage;
use App\Utils\Sms;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SyncSmsCommand extends Command
{
    protected $signature = 'app:sync-sms {--all}';

    protected $description = 'Sync SMS via API';

    public function handle(): void
    {
        $this->option('all')
            ? $this->syncAll()
            : $this->syncToday();
    }

    private function syncAll()
    {
        $start = [
            'start' => now()->subDays(150)->format('d.m.Y'),
        ];

        SmsMessage::truncate();
        foreach (range(1, 3) as $page) {
            $params = isset($prevId) ? [
                ...$start,
                'prev_id' => $prevId,
            ] : $start;
            $history = Sms::history($params);
            $bar = $this->output->createProgressBar(count($history));
            foreach ($history as $item) {
                $this->insert($item);
                $bar->advance();
            }
            $bar->finish();
            $prevId = $history[count($history) - 1]['int_id'];
        }
    }

    private function insert(array $item)
    {
        SmsMessage::insert([
            'id' => $item['id'],
            'number' => $item['phone'],
            'text' => $item['message'],
            'status' => $item['status'],
            'status_name' => $item['status_name'],
            'created_at' => Carbon::parse($item['send_date'])->format('Y-m-d H:i:s'),
        ]);
    }

    private function syncToday()
    {
        $history = Sms::history([
            'start' => now()->format('d.m.Y'),
        ]);

        SmsMessage::whereRaw('DATE(created_at) = CURDATE()')->delete();

        $bar = $this->output->createProgressBar(count($history));
        foreach ($history as $item) {
            // импортируем только сообщения из EC
            if ($item['comment'] !== 'EC') {
                continue;
            }
            $this->insert($item);
            $bar->advance();
        }
        $bar->finish();
    }
}
