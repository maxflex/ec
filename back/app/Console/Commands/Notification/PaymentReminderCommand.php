<?php

namespace App\Console\Commands\Notification;

use App\Enums\TelegramTemplate;
use App\Models\ContractVersionPayment;
use App\Models\TelegramMessage;
use Illuminate\Console\Command;

class PaymentReminderCommand extends Command
{
    protected $signature = 'notification:payment-reminder';

    protected $description = 'Рассылается представителям за 7 дней до предполагаемого платежа';

    public function handle(): void
    {
        $payments = ContractVersionPayment::query()
            ->whereHas('contractVersion', fn ($q) => $q->where('is_active', true))
            ->where('date', now()->addDays(7)->format('Y-m-d'))
            ->get();

        $bar = $this->output->createProgressBar($payments->count());
        foreach ($payments as $payment) {
            $clientParent = $payment->contractVersion->contract->client->parent;
            TelegramMessage::sendTemplate(
                TelegramTemplate::paymentReminder,
                $clientParent,
                [
                    'clientParent' => $clientParent,
                    'payment' => $payment,
                ]
            );
            $bar->advance();
        }
        $bar->finish();
    }
}
