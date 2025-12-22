<?php

namespace App\Utils;

use App\Enums\Company;
use App\Enums\ContractPaymentMethod;
use App\Models\ContractPayment;
use Carbon\Carbon;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

/**
 * Всё для 1с
 */
readonly class OneC
{
    private const string URL_COUNTERAGENTS = 'Catalog_Контрагенты';

    private const string URL_CONTRACTS = 'Catalog_ДоговорыКонтрагентов';

    private const string URL_PAYMENTS = 'Document_ОплатаПлатежнойКартой';

    public function __construct(protected ContractPayment $payment) {}

    /**
     * Получить RefKey по ID из ссылки
     */
    public static function getRefKey(string $idFromLink): string
    {
        $refKey = strtolower(trim($idFromLink));

        if (! preg_match('/^[0-9a-f]{32}$/', $refKey)) {
            throw new InvalidArgumentException('ref must be 32 hex chars');
        }

        $r = str_split($refKey, 2);

        // Перестановка байтов — доказано на твоём объекте
        $g = [];
        $g[0] = $r[12];
        $g[1] = $r[13];
        $g[2] = $r[14];
        $g[3] = $r[15];
        $g[4] = $r[10];
        $g[5] = $r[11];
        $g[6] = $r[8];
        $g[7] = $r[9];
        $g[8] = $r[0];
        $g[9] = $r[1];
        $g[10] = $r[2];
        $g[11] = $r[3];
        $g[12] = $r[4];
        $g[13] = $r[5];
        $g[14] = $r[6];
        $g[15] = $r[7];

        $hex = implode('', $g);

        return substr($hex, 0, 8).'-'.
            substr($hex, 8, 4).'-'.
            substr($hex, 12, 4).'-'.
            substr($hex, 16, 4).'-'.
            substr($hex, 20);
    }

    public static function toShareUrl(string $refKey): string
    {
        $clean = strtolower(str_replace(['-', '{', '}'], '', $refKey));

        if (! preg_match('/^[0-9a-f]{32}$/', $clean)) {
            throw new InvalidArgumentException('GUID must be 32 hex chars');
        }

        // g0..g15
        $g = str_split($clean, 2);

        // r0..r15 — обратная перестановка
        $r = [];
        $r[0] = $g[8];
        $r[1] = $g[9];
        $r[2] = $g[10];
        $r[3] = $g[11];
        $r[4] = $g[12];
        $r[5] = $g[13];
        $r[6] = $g[14];
        $r[7] = $g[15];
        $r[8] = $g[6];
        $r[9] = $g[7];
        $r[10] = $g[4];
        $r[11] = $g[5];
        $r[12] = $g[0];
        $r[13] = $g[1];
        $r[14] = $g[2];
        $r[15] = $g[3];

        return implode('', $r);
    }

    public function sync(): object
    {
        // Сначала проверяем, есть ли контрагент. Если нет – создаём
        $counteragent = $this->getCounteragent() ?? $this->createCounteragent();
        $contract = $this->getContract() ?? $this->createContract($counteragent->Ref_Key);
        $paymentData = $this->getPaymentData();

        $payload = [
            ...$paymentData['root'],
            'Date' => sprintf(
                '%sT00:00:00',
                Carbon::parse($this->payment->date)->format('Y-m-d')
            ),
            'Контрагент' => $counteragent->Ref_Key,
            'Контрагент_Type' => 'StandardODATA.Catalog_Контрагенты',
            'ВидОперации' => $this->payment->is_return ? 'ВозвратПокупателю' : 'ОплатаПокупателя',
            'Комментарий' => sprintf(
                'Создан из CRM (contract_payment_id: %d)',
                $this->payment->id,
            ),
            'СуммаДокумента' => $this->payment->sum,
            'Posted' => ! is_localhost(), // платежи, помеченные к удалению, нельзя Posted=true
            'DeletionMark' => is_localhost(),
            'РасшифровкаПлатежа' => [[
                ...$paymentData['РасшифровкаПлатежа'],
                'LineNumber' => '1',
                'ДоговорКонтрагента_Key' => $contract->Ref_Key,
                'СпособПогашенияЗадолженности' => 'Автоматически',
                'СуммаПлатежа' => $this->payment->sum,
                'СуммаВзаиморасчетов' => $this->payment->sum,
                'КурсВзаиморасчетов' => 1,
            ]],
        ];

        return (object) $this->http()->post(self::URL_PAYMENTS, $payload)->json();
    }

    /**
     * Получить контрагента (представителя) из 1С
     */
    private function getCounteragent(): ?object
    {
        $payload = [
            '$format' => 'json',
            '$filter' => sprintf(
                "НаименованиеПолное eq '%s'",
                $this->payment->contract->client->representative->formatName('full'),
            ),
        ];

        $response = $this->http()->get(self::URL_COUNTERAGENTS, $payload)->json('value');

        return count($response) ? (object) $response[0] : null;
    }

    private function http(): PendingRequest
    {
        return Http::withBasicAuth(config('onec.user'), config('onec.password'))
            ->baseUrl($this->getHost().'/odata/standard.odata/')
            ->acceptJson()
            ->contentType('application/json')
            ->withOptions([
                'proxy' => '37.140.195.195:8888',
                'verify' => false,
                'timeout' => 30,
            ]);
    }

    /**
     * Хост для 1С
     */
    private function getHost(): string
    {
        return match ($this->payment->contract->company) {
            Company::ip => 'https://1cfresh.com/a/ea/1578251',
            Company::ano => 'https://msk1.1cfresh.com/a/npo/2987878',
            Company::ooo => 'https://1cfresh.com/a/ea/1447528',
        };
    }

    /**
     * Создать контрагента (представителя) в 1С
     */
    private function createCounteragent(): object
    {
        $representative = $this->payment->contract->client->representative;

        $payload = [
            'НаименованиеПолное' => $representative->formatName('full'),
            'Description' => $representative->formatName('full'),
            'ЮридическоеФизическоеЛицо' => 'ФизическоеЛицо',
            'Комментарий' => sprintf(
                'Создан из CRM (%s/clients/%d)',
                config('app.frontend_url'),
                $this->payment->contract->client_id,
            ),
            // чтобы созданных с localhost можно было быстро удалить
            'DeletionMark' => is_localhost(),
        ];

        return (object) $this->http()->post(self::URL_COUNTERAGENTS, $payload)->json();
    }

    /**
     * Получить договор из 1С
     */
    private function getContract(): ?object
    {
        $payload = [
            '$format' => 'json',
            '$filter' => sprintf(
                "Номер eq '%d'",
                $this->payment->contract_id,
            ),
        ];

        $response = $this->http()->get(self::URL_CONTRACTS, $payload)->json('value');

        return count($response) ? (object) $response[0] : null;
    }

    /**
     * Создать договор в 1С
     */
    private function createContract(string $counteragentKey): object
    {
        $contractDate = Carbon::parse($this->payment->contract->first_version->date);

        $payload = [
            'Номер' => $this->payment->contract_id,
            'Дата' => $contractDate->format('Y-m-d').'T00:00:00',
            'Description' => sprintf(
                '%d от %s',
                $this->payment->contract_id,
                $contractDate->format('d.m.Y'),
            ),
            'Owner_Key' => $counteragentKey,
            'ВидДоговора' => 'СПокупателем',
            'Комментарий' => sprintf(
                'Создан из CRM (%s/clients/%d?contract_id=%d)',
                config('app.frontend_url'),
                $this->payment->contract->client_id,
                $this->payment->contract_id,
            ),
            // чтобы созданных с localhost можно было быстро удалить
            'DeletionMark' => is_localhost(),
        ];

        return (object) $this->http()->post(self::URL_CONTRACTS, $payload)->json();
    }

    /**
     * Реквизиты для создания платежа в 1С
     *
     * @return array{root: array, РасшифровкаПлатежа: array}
     */
    private function getPaymentData()
    {
        return match ($this->payment->contract->company) {
            Company::ano => [
                'root' => [
                    // Эквайринг ПАО СБЕРБАНК
                    'ВидОплаты_Key' => 'ca6dda3e-c6c8-11f0-84e3-fa163e1f3769',
                ],
                'РасшифровкаПлатежа' => [
                    'СтавкаНДС' => 'БезНДС',
                    'СуммаНДС' => 0,
                ],
            ],
            Company::ip => [
                'root' => [
                    'ВидОплаты_Key' => $this->payment->method === ContractPaymentMethod::sbpOnline
                        // Юмани
                        ? '4bfb6328-8d76-11f0-91d2-fa163ea2e44c'
                        // Эквайринг АО "Альфа-Банк"/
                        : 'f0394772-16e6-11f0-9154-fa163ea2e44c',
                ],
                'РасшифровкаПлатежа' => [
                    'СтавкаНДС' => 'НДС5_105',
                    'СуммаНДС' => round($this->payment->sum * (5 / 105), 2),
                ],
            ],
            Company::ooo => [
                'root' => [
                    // Эквайринг Альфа-Банка/3
                    'ВидОплаты_Key' => '624dbbde-49f2-11f0-8c82-fa163e4a3a63',
                ],
                'РасшифровкаПлатежа' => [
                    'СтавкаНДС' => 'БезНДС',
                    'СуммаНДС' => 0,
                ],
            ],
        };
    }
}
