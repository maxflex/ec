<?php

namespace App\Http\Controllers\Pub;

use App\Enums\ContractPaymentMethod;
use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractPayment;
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

        $payment = $client->createPayment(
            [
                'amount' => [
                    'value' => $request->input('amount'),
                    'currency' => 'RUB',
                ],
                'confirmation' => [
                    'type' => 'redirect',
                    'return_url' => config('app.frontend_url').'/_test/sbp',
                ],
                'payment_method_data' => [
                    'type' => 'sbp',
                ],
                'capture' => true,
                'description' => sprintf(
                    'Платные образовательные услуги по договору №%d от %sг.',
                    $contract->id,
                    Carbon::parse($contract->active_version->date)->format('d.m.Y')
                ),
            ]
        );

        $contract->payments()->create([
            'sum' => $amount,
            'date' => now()->format('Y-m-d'),
            'method' => ContractPaymentMethod::sbp,
            'sbp_id' => $payment->getId(),
        ]);

        return [
            'url' => $payment->getConfirmation()->getConfirmationUrl(),
        ];
    }

    /**
     * Обработка уведомлений от ЮКасса
     */
    public function notification(Request $request)
    {
        if (is_localhost()) {
            logger('SBP', $request->all());
        }

        $contractPayment = ContractPayment::query()
            ->where('sbp_id', $request->object['id'])
            ->first();

        if ($contractPayment === null) {
            return;
        }

        switch ($request->event) {
            case NotificationEventType::PAYMENT_SUCCEEDED:
                $contractPayment->update(['is_confirmed' => true]);
                break;

            case NotificationEventType::PAYMENT_CANCELED:
                $contractPayment->delete();
                break;
        }
    }
}
