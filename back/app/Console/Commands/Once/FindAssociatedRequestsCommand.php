<?php

namespace App\Console\Commands\Once;

use App\Models\Phone;
use App\Models\Request;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

/**
 * TMP: delete after use
 * под связанными заявками нужно понимать заявки, которые ассоциированы с ассоциированными клиентами и имеют другой телефон
 * пример: заявка 1000 содержит телефон 500, клиент содержит телефоны 500, 501, 502. Есть заявка 1500 в телефоном 502. Заявки 1500 и 1000 связаны
 *
 */
class FindAssociatedRequestsCommand extends Command
{
    protected $signature = 'app:once:find-associated-requests';

    protected $description = 'Command description';

    public function handle(): void
    {
        $result = [];
        $requests = Request::where('created_at', '>', now()->subYear()->format('Y-m-d'))->get();
        $bar = $this->output->createProgressBar($requests->count());
        foreach ($requests as $request) {
            $requestNumbers = $request->phones->pluck('number');
            foreach ($request->getAssociatedClients() as $client) {
                $ids = $this->getAssociatedRequests($client->phones, $requestNumbers);
                if ($ids->count()) {
                    @$result[$request->id]['clients-' . $client->id] = $ids->join(', ');
                }
                $ids = $this->getAssociatedRequests($client->parent->phones, $requestNumbers);
                if ($ids->count()) {
                    @$result[$request->id]['parents-' . $client->parent->id] = $ids->join(', ');
                }
            }
            $bar->advance();
        }
        $bar->finish();
        $this->line(PHP_EOL);
        $this->info("Total: " . count($result));
        $this->line(PHP_EOL);
        foreach ($result as $requestId => $data) {
            foreach ($data as $str => $ids) {
                [$entityType, $entityId] = explode('-', $str);
                $this->line(join("\t", [
                    $requestId,
                    $ids,
                    ($entityType === 'clients' ? 'клиент' : 'представитель') . ' ' . $entityId,
                    "https://v3.ege-centr.ru/$entityType/$entityId"
                ]));
            }
        }
    }

    /**
     * @param Collection<int, Phone> $phones
     * @param Collection<int, string> $requestNumbers
     * @return Collection<int, int>
     */
    private function getAssociatedRequests($phones, $requestNumbers)
    {
        $numbers = $phones->pluck('number')
            ->filter(fn($number) => !$requestNumbers->contains($number));

        return Phone::whereIn('number', $numbers)
            ->where('entity_type', Request::class)
            ->pluck('entity_id');
    }
}
