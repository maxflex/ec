<?php

namespace App\Utils;

use App\Enums\ContractPaymentMethod;
use App\Models\Contract;
use App\Models\ContractPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

readonly class AlfaPayment
{
    private const string CACHE_KEY = 'alfa-payment';

    /**
     * Обработка данных вебхука от Альфы
     */
    public static function handleWebhook(Request $request)
    {
        $events = $request->all();
        $count = 0;

        foreach ($events as $event) {
            logger('ALFA', $event);

            // 1. Проверяем направление (нужны только входящие деньги)
            if (data_get($event, 'data.direction') !== 'CREDIT') {
                continue;
            }

            if (data_get($event, 'actionType') !== 'create') {
                continue;
            }

            // 3. Назначение
            $purpose = (string) data_get($event, 'data.paymentPurpose', '');

            if (! preg_match('/договор\D*(\d{5})/miu', $purpose, $m)) {
                Log::warning('ALFA WEBHOOK: regex mismatch', [
                    'purpose' => $purpose,
                ]);

                continue;
            }

            $contractId = (int) $m[1];

            $contract = Contract::find($contractId);

            if (! $contract) {
                Log::warning('ALFA WEBHOOK: contract not found', [
                    'contract_id' => $contractId,
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
            $sum = data_get($event, 'data.amountRub.amount') ?? data_get($event, 'data.amount.amount');

            $sum = (int) round((float) $sum);

            // 5. Дата
            $date = data_get($event, 'data.rurTransfer.valueDate') ?? data_get($event, 'data.documentDate');

            // 6. Внешний ID
            $externalId = data_get($event, 'data.uuid');
            // data_get($event, 'data.transactionId')

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
                Log::warning('ALFA WEBHOOK: idempotency', [
                    'contract_id' => $contractId,
                    'purpose' => $purpose,
                    'external_id' => $externalId,
                ]);

                continue;
            }

            self::saveToCache($externalId, $contractId, $sum, $date, $purpose);
            $count++;
        }

        return response()->json([
            'status' => 'ok',
            'count' => $count,
        ]);
    }

    /**
     * Сохранить платеж из вебхука в кэш
     */
    private static function saveToCache(string $externalId, int $contractId, int $sum, string $date, string $purpose)
    {
        cache()->put(
            self::cacheKey($externalId),
            [
                'contract_id' => $contractId,
                'external_id' => $externalId,
                'sum' => $sum,
                'date' => $date,
                'purpose' => $purpose,
            ],
            now()->addMonth()
        );
    }

    private static function cacheKey(string $externalId)
    {
        return self::CACHE_KEY.':'.$externalId;
    }

    /**
     * Получить все платежи, сохраненные в кэше
     *
     * @return Collection<int, ContractPayment>
     */
    public static function getAllPayments(): Collection
    {
        // @phpstan-ignore-next-line
        $rawKeys = cache()->connection()->keys('*'.self::CACHE_KEY.'*');

        $items = collect();
        $id = -1;
        foreach ($rawKeys as $rawKey) {
            // обрезать префикс в rawKey
            $key = self::CACHE_KEY.str($rawKey)->after(self::CACHE_KEY);

            $data = cache()->get($key);
            if ($data === null) {
                continue;
            }

            $contractPayment = new ContractPayment([
                ...$data,
                'is_return' => false,
                'is_confirmed' => false,
                'is_1c_synced' => false,
                'method' => ContractPaymentMethod::bill,
            ]);
            $contractPayment->setAttribute('id', $id);
            $contractPayment->setAttribute('purpose', $data['purpose']);
            $items->push($contractPayment);
            $id--;
        }

        return $items;
    }

    /**
     * Удалить платеж из кэша
     */
    public static function removeFromCache(ContractPayment $contractPayment): bool
    {
        if (! $contractPayment->external_id) {
            return false;
        }

        return cache()->forget(self::cacheKey($contractPayment->external_id));
    }
}
