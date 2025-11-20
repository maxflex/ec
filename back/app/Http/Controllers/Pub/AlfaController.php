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
        $payload = $request->all();

        logger('Alfa', $payload);

        // 1. Проверяем, что это входящий платёж (деньги пришли нам)
        if (data_get($payload, 'data.direction') !== 'CREDIT') {
            return response()->json(['status' => 'ignored_not_credit']);
        }

        // 2. (Опционально) проверка, что деньги пришли именно на наш счёт
        // $ourAccount = config('services.alfa.account'); // задай в config/services.php
        //
        // if ($ourAccount && data_get($payload, 'data.rurTransfer.payeeAccount') !== $ourAccount) {
        //     return response()->json(['status' => 'ignored_wrong_account']);
        // }

        // 3. Назначение платежа
        $purpose = (string) data_get($payload, 'data.paymentPurpose', '');

        // Обрабатываем только платежи с назначением "ПЛАТНЫЕ ОБРАЗОВАТЕЛЬНЫЕ УСЛУГИ ПО ДОГОВОРУ"
        if (! preg_match('/ПЛАТНЫЕ ОБРАЗОВАТЕЛЬНЫЕ УСЛУГИ ПО ДОГОВОРУ №(\d+)/iu', $purpose, $m)) {
            return response()->json(['status' => 'ignored_no_mask']);
        }

        $contractId = (int) $m[1];

        $contract = Contract::find($contractId);

        if (! $contract) {
            Log::warning('Alfa webhook: contract not found', [
                'contract_id' => $contractId,
                'purpose' => $purpose,
                'payload' => $payload,
            ]);

            return response()->json(['status' => 'contract_not_found']);
        }

        // 5. Сумма и дата
        // Берём amountRub если есть, иначе обычный amount
        $amount = data_get($payload, 'data.amountRub.amount') ?? data_get($payload, 'data.amount.amount');

        $amount = (int) round((float) $amount);

        // Дата платежа — valueDate, если есть, иначе documentDate
        $date = data_get($payload, 'data.rurTransfer.valueDate') ?? data_get($payload, 'data.documentDate');

        // 6. Внешний ID от Альфы
        $externalId = data_get($payload, 'data.transactionId') ?? data_get($payload, 'data.uuid');

        if (! $externalId) {
            // На всякий случай логируем, но можно и продолжать без external_id
            Log::warning('Alfa webhook: no external id', [
                'contract_id' => $contractId,
                'purpose' => $purpose,
                'payload' => $payload,
            ]);

            return response()->json(['status' => 'no_external_id']);
        }

        ContractPayment::create([
            'contract_id' => $contractId,
            'external_id' => $externalId,
            'sum' => $amount,
            'date' => $date,
            'method' => ContractPaymentMethod::bill,
        ]);

        return response()->json(['status' => 'ok']);
    }
}

/**
 * [
 * {
 * "actionType": "create",
 * "eventTime": "2025-11-20T12:02:46.293Z",
 * "object": "ul_transaction_default",
 * "sub": "364b83ce-762b-479a-87f5-af82a684aae6",
 * "organizationId": "24646ab02cf00297dfd256659e5ffc3fbaab934bdde287eeea02ad0ab99e07b4",
 * "data": {
 * "amount": {
 * "amount": 40.0,
 * "currencyName": "RUR"
 * },
 * "amountRub": {
 * "amount": 40.0,
 * "currencyName": "RUR"
 * },
 * "correspondingAccount": "30101810200000000593",
 * "direction": "CREDIT",
 * "documentDate": "2025-11-20",
 * "filial": "АО \"АЛЬФА-БАНК\" г Москва",
 * "number": "240",
 * "operationCode": "01",
 * "operationDate": "2025-11-20T12:02:45Z",
 * "paymentPurpose": "В том числе НДС 20%, 2.17 руб. ТЕСТ JMON Generated at 2025-11-20T12:02:45.186207106",
 * "priority": "5",
 * "uuid": "7d6ec193-50af-31d7-b650-9b233d891f29",
 * "transactionId": "1221207MOCO#DS4000010",
 * "debtorCode": "00000",
 * "extendedDebtorCode": "50012008",
 * "rurTransfer": {
 * "deliveryKind": "электронно",
 * "departmentalInfo": {
 * "uip": "18880077170010295651",
 * "drawerStatus101": "01",
 * "kbk": "39210202010061000160",
 * "oktmo": "11605000",
 * "reasonCode106": "0",
 * "taxPeriod107": "03.10.2020",
 * "docNumber108": "123",
 * "docDate109": "20.11.25",
 * "paymentKind110": "1"
 * },
 * "payeeAccount": "40702810102300000001",
 * "payeeBankBic": "044525593",
 * "payeeBankCorrAccount": "30101810200000000593",
 * "payeeBankName": "АО \"АЛЬФА-БАНК\" г Москва",
 * "payeeInn": "0665413230",
 * "payeeKpp": "011206020",
 * "payeeName": "Общество с ограниченной ответственностью \"Ромашка\"",
 * "payerAccount": "47423810601300000169",
 * "payerBankBic": "044525593",
 * "payerBankCorrAccount": "30101810200000000593",
 * "payerBankName": "АО \"АЛЬФА-БАНК\" г Москва",
 * "payerInn": "0140237176",
 * "payerKpp": "037186025",
 * "payerName": "Полное наименование Орг № 11329",
 * "payingCondition": "1",
 * "purposeCode": "2",
 * "receiptDate": "2025-11-20",
 * "valueDate": "2025-11-20"
 * }
 * }
 * },
 * {
 * "actionType": "create",
 * "eventTime": "2025-11-20T12:02:46.565Z",
 * "object": "ul_transaction_default",
 * "sub": "364b83ce-762b-479a-87f5-af82a684aae6",
 * "organizationId": "24646ab02cf00297dfd256659e5ffc3fbaab934bdde287eeea02ad0ab99e07b4",
 * "data": {
 * "amount": {
 * "amount": 0.4,
 * "currencyName": "EUR"
 * },
 * "amountRub": {
 * "amount": 40.0,
 * "currencyName": "RUR"
 * },
 * "correspondingAccount": "30101810200000000593",
 * "direction": "CREDIT",
 * "documentDate": "2025-11-20",
 * "filial": "АО \"АЛЬФА-БАНК\" г Москва",
 * "number": "240",
 * "operationCode": "01",
 * "operationDate": "2025-11-20T12:02:45Z",
 * "paymentPurpose": "В том числе НДС 20%, 2.17 руб. ТЕСТ JMON Generated at 2025-11-20T12:02:45.187547743",
 * "priority": "5",
 * "uuid": "ec3fee6b-708d-3a68-84d9-f6543e36727c",
 * "transactionId": "1221207MOCO#DS4000013",
 * "debtorCode": "00000",
 * "extendedDebtorCode": "50012008",
 * "rurTransfer": {
 * "deliveryKind": "электронно",
 * "departmentalInfo": {
 * "uip": "18880077170010295651",
 * "drawerStatus101": "01",
 * "kbk": "39210202010061000160",
 * "oktmo": "11605000",
 * "reasonCode106": "0",
 * "taxPeriod107": "03.10.2020",
 * "docNumber108": "123",
 * "docDate109": "20.11.25",
 * "paymentKind110": "1"
 * },
 * "payeeAccount": "40702978902300000004",
 * "payeeBankBic": "044525593",
 * "payeeBankCorrAccount": "30101810200000000593",
 * "payeeBankName": "АО \"АЛЬФА-БАНК\" г Москва",
 * "payeeInn": "0665413230",
 * "payeeKpp": "011206020",
 * "payeeName": "Общество с ограниченной ответственностью \"Ромашка\"",
 * "payerAccount": "47423810601300000169",
 * "payerBankBic": "044525593",
 * "payerBankCorrAccount": "30101810200000000593",
 * "payerBankName": "АО \"АЛЬФА-БАНК\" г Москва",
 * "payerInn": "0140237176",
 * "payerKpp": "037186025",
 * "payerName": "Полное наименование Орг № 11329",
 * "payingCondition": "1",
 * "purposeCode": "2",
 * "receiptDate": "2025-11-20",
 * "valueDate": "2025-11-20"
 * }
 * }
 * }
 * ]
 */
