<?php

namespace App\Http\Controllers\Pub;

use App\Enums\ContractPaymentMethod;
use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractPayment;
use Illuminate\Http\Request;
use Log;

/**
 * Вебхуки от Альфа-Банка
 */
class AlfaController extends Controller
{
    public function __invoke(Request $request)
    {
        $events = $request->all();

        foreach ($events as $event) {
            logger('ALFA', $event);

            // 1. Проверяем направление (нужны только входящие деньги)
            if (data_get($event, 'data.direction') !== 'CREDIT') {
                continue;
            }

            // 3. Назначение
            $purpose = (string) data_get($event, 'data.paymentPurpose', '');

            if (! preg_match('/договор[\D]?[\s]?[\D]?(\d{5,7})/miu', $purpose, $m)) {
                continue;
            }

            $contractId = (int) $m[1];

            $contract = Contract::find($contractId);

            if (! $contract) {
                Log::warning('ALFA WEBHOOK: contract not found', [
                    'contract_id' => $contractId,
                    'purpose' => $purpose,
                    'event' => $event,
                ]);

                continue;
            }

            // Деньги должны приходить на правильный счет
            $payeeAccount = data_get($event, 'data.rurTransfer.payeeAccount');
            $contractAccount = $contract->company->getAccountNumber();
            if ($payeeAccount !== $contractAccount) {
                Log::warning('ALFA WEBHOOK: accounts mismatch', [
                    'payeeAccount' => $payeeAccount,
                    'contractAccount' => $contractAccount,
                ]);

                continue;
            }

            // 4. Сумма
            $amount = data_get($event, 'data.amountRub.amount') ?? data_get($event, 'data.amount.amount');

            $amount = (int) round((float) $amount);

            // 5. Дата
            $date = data_get($event, 'data.rurTransfer.valueDate') ?? data_get($event, 'data.documentDate');

            // 6. Внешний ID
            $externalId = data_get($event, 'data.transactionId') ?? data_get($event, 'data.uuid');

            if (! $externalId) {
                Log::warning('ALFA WEBHOOK: no external id', [
                    'contract_id' => $contractId,
                    'purpose' => $purpose,
                    'event' => $event,
                ]);

                continue;
            }

            // 7. Идемпотентность по external_id (очень желательно)
            if (ContractPayment::where('external_id', $externalId)->exists()) {
                continue;
            }

            cache()->tags('alfa-webhook')->put(
                $externalId,
                [
                    'contract_id' => $contractId,
                    'external_id' => $externalId,
                    'sum' => $amount,
                    'date' => $date,
                    'method' => ContractPaymentMethod::bill,
                ],
                now()->addMonth()
            );
        }

        return response()->json(['status' => 'ok']);
    }
}
