<?php

namespace App\Console\Commands\Once;

use App\Models\Comment;
use App\Models\Request;
use Illuminate\Console\Command;

class RequestCommentsCommand extends Command
{
    protected $signature = 'once:request-comments';

    protected $description = 'Command description';

    public function handle(): void
    {
        $comments = Comment::query()
            ->where('entity_type', Request::class)
            ->whereBetween('created_at', [
                '2025-01-01 00:00:00',
                '2025-06-30 23:59:59',
            ])
            ->get();

        $result = collect();

        $result->push([
            'id',
            'user_id',
            'text',
            'created_at',
            'request_id',
            'request_status',
            'client_ids',
            'contract_years',
        ]);

        $bar = $this->output->createProgressBar($comments->count());
        foreach ($comments as $c) {
            $r = Request::find($c->entity_id);
            if ($r === null) {
                $bar->advance();

                continue;
            }
            $clients = $r->getAssociatedClients();

            $contracts = collect();
            foreach ($clients as $client) {
                foreach ($client->contracts as $contract) {
                    $contracts->push($contract->year);
                }
            }

            $result->push([
                $c->id,
                $c->user_id,
                $this->sanitizeForTsv($c->text),
                $c->created_at,
                $c->entity_id,
                $r->status->value,
                $clients->pluck('id')->join(', '),
                $contracts->join(', '),
            ]);
            $bar->advance();
        }

        $bar->finish();

        $this->line(PHP_EOL);
        $url = save_csv($result);

        dd($url);
    }

    private function sanitizeForTsv(string $text): string
    {
        return preg_replace('/[\t\r\n]+/', ' ', trim($text));
    }
}
