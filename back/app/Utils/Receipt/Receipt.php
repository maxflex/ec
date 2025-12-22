<?php

namespace App\Utils\Receipt;

use App\Enums\Company;
use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Отправка чеков
 */
readonly class Receipt
{
    private PendingRequest $http;

    private array $config;

    public function __construct(private ReceiptData $data)
    {
        $this->config = config('receipt.'.$data->company->value);

        $options = [
            'headers' => [
                'Content-Type' => 'application/json; charset=utf-8',
            ],
        ];

        // Для АНО отправляем чек через прокси, меняя IP-адрес
        if ($data->company === Company::ano) {
            // IP, с которого будет уходить чек
            // Можно проверить из php artisan tinker
            // Http::withOptions(['proxy' => '188.166.167.107:8888'])->get('https://api.ipify.org')->body();
            $options['proxy'] = $this->config['ip'].':8888';
        }

        $this->http = Http::baseUrl($this->config['base_url'])->withOptions($options);
    }

    public function send(): bool
    {
        $token = $this->getToken();
        $payload = $this->buildPayload();
        $groupCode = $this->config['group_code'];

        // Меняем операцию в зависимости от флага
        // sell - приход [cite: 108]
        // sell_refund - возврат прихода [cite: 110]
        $operation = $this->data->isReturn ? 'sell_refund' : 'sell';

        // Отправка запроса "Приход" (sell) [cite: 108]
        // Важно: Токен передаем в заголовке 'Token', а не Bearer [cite: 101]
        $response = $this->http
            ->withHeaders(['Token' => $token])
            ->post("/{$groupCode}/{$operation}", $payload);

        if ($response->failed()) {
            // В v5 текст ошибки приходит в ['error']['text'] [cite: 88, 565]
            $errorText = $response['error']['text'] ?? $response->body();
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
        $key = 'receipt_token_'.$this->data->company->value;

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
        // Тип оплаты: 1 - безналичный, 0 - наличные [cite: 353]
        // $paymentType = $this->contractPayment->method === ContractPaymentMethod::cash ? 0 : 1;
        // пока только для онлайн-оплат, в будущем можно обсудить наличка + отказ от бумажного чека
        $paymentType = 1;

        return [
            'timestamp' => now()->format('d.m.Y H:i:s'), // [cite: 249]
            'external_id' => $this->data->externalId,
            'receipt' => [
                'client' => [
                    'phone' => $this->data->receiptNumber,
                    'name' => $this->data->name,
                ],
                'company' => [
                    'email' => 'acc@ege-centr.ru',
                    'sno' => $this->config['sno'],             // Обязательно [cite: 262]
                    'inn' => $this->config['inn'],             // Обязательно [cite: 262]
                    'payment_address' => $this->config['website'],
                ],
                'items' => [
                    [
                        'name' => $this->data->purpose,
                        'price' => $this->data->sum,
                        'quantity' => 1.0,      // [cite: 265]
                        'measure' => 0,         // 0 соотв. "piece" (штуки)
                        'sum' => $this->data->sum,
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
                        'sum' => $this->data->sum,
                    ],
                ],
                'total' => $this->data->sum,
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
