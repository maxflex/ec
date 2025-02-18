<?php

namespace App\Console\Commands\Helper;

use App\Enums\LogType;
use App\Models\Log;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RevertLogCommand extends Command
{
    protected $signature = 'helper:revert-log';

    protected $description = 'Откатить состояние до лога по ID';

    public function handle(): void
    {
        $logId = $this->ask('Log ID');
        $logId = (int) $logId;
        $log = Log::query()
            ->where('id', $logId)
            ->where('type', LogType::update)
            ->first();

        $confirmed = $this->confirm('This log?'.PHP_EOL.json_encode($log->toArray(), JSON_PRETTY_PRINT));
        if (! $confirmed) {
            return;
        }

        $update = [];
        foreach ($log->data as $d) {
            $update[$d['field']] = $d['old'];
        }

        dump($update);

        DB::table($log->table)->whereId($log->row_id)->update($update);
        $this->line('Updated!');
    }
}
