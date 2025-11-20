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

        // для чека
        $fullName = $contract->client->representative->formatName('full');
        $phone = is_localhost() ? '79252727210' : $contract->client->representative->getPhoneNumbers()->first();

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
                'receipt' => [
                    'customer' => [
                        'full_name' => $fullName,
                        'phone' => $phone,
                    ],
                    'items' => [
                        [
                            'description' => $description,
                            'quantity' => 1.0,
                            'amount' => [
                                'value' => $amount,
                                'currency' => 'RUB',
                            ],
                            'vat_code' => 9,
                            'payment_subject' => 'service',
                            'payment_mode' => 'full_prepayment',
                            'measure' => 'piece',
                            'type' => 'prepayment',
                        ],
                    ],
                ],
                'description' => $description,
            ]
        );

        if (is_localhost()) {
            logger(json_encode($payment, JSON_PRETTY_PRINT));
        }

        $contractPayment = $contract->payments()->make([
            'sum' => $amount,
            'date' => now()->format('Y-m-d'),
            'method' => ContractPaymentMethod::sbpOnline,
            'external_id' => $payment->getId(),
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
