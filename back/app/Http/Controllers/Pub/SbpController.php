<?php

namespace App\Http\Controllers\Pub;

use App\Enums\ContractPaymentMethod;
use App\Http\Controllers\Controller;
use App\Models\Contract;
use Carbon\Carbon;
use Illuminate\Http\Request;
use YooKassa\Client;
use YooKassa\Model\Notification\NotificationEventType;

class SbpController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'contract_id' => ['required', 'exists:contracts,id'],
            'amount' => ['required', 'numeric'],
            'number' => ['required', 'phone'],
        ]);

        $amount = intval($request->amount);
        $contract = Contract::find($request->contract_id);

        $client = new Client;
        $client->setAuth(config('sbp.shop_id'), config('sbp.api_key'));

        $description = sprintf(
            'Платные образовательные услуги по договору №%d от %sг.',
            $contract->id,
            Carbon::parse($contract->active_version->date)->format('d.m.Y')
        );

        $payment = $client->createPayment(
            [
                'amount' => [
                    'value' => $amount,
                    'currency' => 'RUB',
                ],
                'confirmation' => [
                    'type' => 'qr',
                    'return_url' => config('app.frontend_url').'/billing',
                ],
                'payment_method_data' => [
                    'type' => 'sbp',
                ],
                'capture' => true,
                'description' => $description,
                'receipt' => [
                    'customer' => [
                        'full_name' => $contract->client->representative->formatName('full'),
                        'phone' => $request->input('number'),
                    ],
                    'items' => [
                        [
                            'description' => $description,
                            'quantity' => 1.0,
                            'amount' => [
                                'value' => $amount,
                                'currency' => 'RUB',
                            ],
                            // НДС по расчетной ставке 5/105
                            // https://yookassa.ru/developers/payment-acceptance/receipts/54fz/other-services/parameters-values
                            'vat_code' => 9,
                            // Частичная предоплата
                            'payment_mode' => 'partial_prepayment',
                            'payment_subject' => 'service',
                            'measure' => 'piece',
                            'type' => 'prepayment',
                        ],
                    ],
                ],
            ]
        );

        if (is_localhost()) {
            logger(json_encode($payment, JSON_PRETTY_PRINT));
        }

        $contractPayment = $contract->payments()->make([
            'sum' => $amount,
            'date' => now()->format('Y-m-d'),
            'method' => ContractPaymentMethod::sbpOnline,
            'receipt_number' => is_localhost() ? '79252727210' : $request->input('number'),
        ]);

        cache()->put('sbp-'.$payment->getId(), $contractPayment, now()->addHour());

        return [
            'url' => $payment->getConfirmation()->getConfirmationData(),
        ];
    }

    /**
     * Обработка уведомлений от ЮКасса
     */
    public function notification(Request $request)
    {
        if (is_localhost()) {
            logger(json_encode($request->all(), JSON_PRETTY_PRINT));
        }

        $paymentId = $request->object['payment_id'] ?? $request->object['id'];

        $contractPayment = cache()->get('sbp-'.$paymentId);

        // Платежи могут быть также из MR. Их не обрабатывать
        // https://ege-repetitor.ru/payment
        if ($contractPayment === null) {
            return;
        }

        switch ($request->event) {
            case NotificationEventType::PAYMENT_SUCCEEDED:
                $contractPayment->save();
                break;

                // case NotificationEventType::PAYMENT_CANCELED:
                //     $contractPayment->delete();
                //     break;

            case NotificationEventType::REFUND_SUCCEEDED:
                $refund = $contractPayment->replicate();
                $refund->is_return = true;
                $refund->save();
                break;
        }
    }
}
