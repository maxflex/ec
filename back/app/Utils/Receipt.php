<?php

namespace App\Utils;

use App\Enums\ContractPaymentMethod;
use App\Models\ContractPayment;
use Exception;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

/**
 * Отправка чеков
 */
readonly class Receipt
{
    private Factory|PendingRequest $http;

    private array $config;

    public function __construct(public ContractPayment $contractPayment)
    {
        $this->config = config('receipt.'.$this->contractPayment->contract->company->value);
        $this->http = Http::baseUrl($this->config['base_url'])->withHeaders([
            'Content-Type' => 'application/json; charset=utf-8',
        ]);
    }

    public function send(): bool
    {
        $token = $this->getToken();
        $payload = $this->buildPayload();
        $groupCode = $this->config['group_code'];

        // Меняем операцию в зависимости от флага
        // sell - приход [cite: 108]
        // sell_refund - возврат прихода [cite: 110]
        $operation = $this->contractPayment->is_return ? 'sell_refund' : 'sell';

        // Отправка запроса "Приход" (sell) [cite: 108]
        // Важно: Токен передаем в заголовке 'Token', а не Bearer [cite: 101]
        $response = $this->http
            ->withHeaders(['Token' => $token])
            ->post("/{$groupCode}/{$operation}", $payload);

        if ($response->failed()) {
            // В v5 текст ошибки приходит в ['error']['text'] [cite: 88, 565]
            $errorText = $response['error']['text'] ?? $response->body();
            $this->contractPayment->update([
                'receipt_sent_to' => null,
            ]);
            throw new Exception('RECEIPT: '.$errorText);
        }

        logger('RECEIPT');
        logger(print_r($response->json(), true));

        // Возвращаем UUID [cite: 479]
        // return $response['uuid'];
        return true;
    }

    /**
     * Получение токена (API v5)
     * Кэшируем токен на 23 часа (живет 24 часа)
     */
    private function getToken(): string
    {
        $key = 'receipt_token_'.$this->contractPayment->contract->company->value;

        return cache()->remember($key, 82800, function () {
            $response = $this->http->post('/getToken', [
                'login' => $this->config['login'],
                'pass' => $this->config['pass'],
            ]);

            if ($response->failed() || ! isset($response['token'])) {
                throw new Exception('Ошибка авторизации АТОЛ: '.($response['error']['text'] ?? 'Unknown'));
            }

            return $response['token'];
        });
    }

    /**
     * Формирование тела запроса JSON
     */
    private function buildPayload(): array
    {
        $contract = $this->contractPayment->contract;

        $description = sprintf(
            'Платные образовательные услуги по договору №%d от %sг.',
            $contract->id,
            Carbon::parse($contract->active_version->date)->format('d.m.Y')
        );

        // Тип оплаты: 1 - безналичный, 0 - наличные [cite: 353]
        // $paymentType = $this->contractPayment->method === ContractPaymentMethod::cash ? 0 : 1;
        // пока только для онлайн-оплат, в будущем можно обсудить наличка + отказ от бумажного чека
        $paymentType = 1;

        // ФИО клиента
        $fullName = $contract->client->representative->formatName('full');
        $phone = is_localhost() ? '+79252727210' : '+'.Phone::autoCorrectFirstDigit($this->contractPayment->receipt_sent_to);

        // Сумма
        $sum = $this->contractPayment->sum;

        return [
            'timestamp' => now()->format('d.m.Y H:i:s'), // [cite: 249]
            'external_id' => $this->contractPayment->id,
            'receipt' => [
                'client' => [
                    'phone' => $phone,
                    'name' => $fullName, // [cite: 259]
                ],
                'company' => [
                    'email' => 'acc@ege-centr.ru',
                    'sno' => $this->config['sno'],             // Обязательно [cite: 262]
                    'inn' => $this->config['inn'],             // Обязательно [cite: 262]
                    'payment_address' => 'https://ege-centr.ru',
                ],
                'items' => [
                    [
                        'name' => $description, // Из SBP логики [cite: 265]
                        'price' => $sum,
                        'quantity' => 1.0,      // [cite: 265]
                        'measure' => 0,         // 0 соотв. "piece" (штуки)
                        'sum' => $sum,
                        'payment_method' => 'prepayment',
                        'payment_object' => 4,  // 4 = услуга (payment_subject='service' из SBP)
                        'vat' => [
                            'type' => $this->config['vat'],
                        ],
                    ],
                ],
                'payments' => [
                    [
                        'type' => $paymentType,
                        'sum' => $sum,
                    ],
                ],
                'total' => $sum,
            ],
        ];
    }

    public function testReport(string $uuid)
    {
        $token = $this->getToken();
        $groupCode = $this->config['group_code'];

        // Отправка запроса "Приход" (sell) [cite: 108]
        // Важно: Токен передаем в заголовке 'Token', а не Bearer [cite: 101]
        // В документации URL для получения отчета: /report/<uuid>, а не /receipt
        $response = $this->http
            ->withHeaders(['Token' => $token])
            ->get("/{$groupCode}/report/{$uuid}");

        logger(print_r($response->json(), true));
    }
}
