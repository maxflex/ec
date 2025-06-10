<?php

namespace App\Utils;

use Illuminate\Support\Facades\Http;

class Spb
{
    public function getQrCode(): string
    {
        $payload = [
            'command' => 'GetQRCd',
            'TermNo' => '30003530',
            'qrcType' => '01',
            'amount' => '100',
            'currency' => 'RUB',
            'paymentPurpose' => 'Назначение платежа',
        ];

        $json = $this->normalizeJson($payload);
        $signature = $this->sign($json);

        $response = Http::withOptions([
            'cert' => storage_path('certs/mtls.crt'),
            'ssl_key' => storage_path('certs/mtls.key'),
            'verify' => storage_path('certs/ca_bundle.pem'),
        ])->withHeaders([
            'Authorization' => $signature,
            'key-name' => '9701038111_ooo-ege-tsentr_test_28.05.2035',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->post('https://217.12.103.132:2443/fsCryptoProxy', $json);

        return $response->body();
    }

    protected function normalizeJson(array $data): string
    {
        // ВАЖНО: порядок ключей должен быть сохранён вручную
        $ordered = [
            'command' => $data['command'],
            'TermNo' => $data['TermNo'],
            'qrcType' => $data['qrcType'],
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'paymentPurpose' => $data['paymentPurpose'],
        ];

        return json_encode($ordered, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    protected function sign(string $message): string
    {
        $keyPath = storage_path('certs/client.key');

        $dataFile = tempnam(sys_get_temp_dir(), 'sbp_block_');
        $sigFile = tempnam(sys_get_temp_dir(), 'sbp_sig_');

        file_put_contents($dataFile, $message);

        $cmd = "openssl dgst -sha256 -sign {$keyPath} -out {$sigFile} {$dataFile}";
        exec($cmd, $output, $code);

        if ($code !== 0) {
            throw new \RuntimeException('OpenSSL signing failed: '.implode("\n", $output));
        }

        $signature = base64_encode(file_get_contents($sigFile));

        unlink($dataFile);
        unlink($sigFile);

        return $signature;
    }
}
