<?php

namespace App\Utils;

class TelegramMiniApp
{
    private string $checksum;

    private string $sortedInitData;

    public function __construct(string $initData)
    {
        [$checksum, $sortedInitData] = $this->convertInitData($initData);
        $this->checksum = $checksum;
        $this->sortedInitData = $sortedInitData;
    }

    /**
     * convert init data to `key=value` and sort it `alphabetically`.
     *
     * @param  string  $initData  init data from Telegram (`Telegram.WebApp.initData`)
     * @return string[] return hash and sorted init data
     */
    private function convertInitData(string $initData): array
    {
        $initDataArray = explode('&', rawurldecode($initData));
        $needle = 'hash=';
        $hash = '';

        foreach ($initDataArray as &$data) {
            if (str_starts_with($data, $needle)) {
                $hash = substr_replace($data, '', 0, strlen($needle));
                $data = null;
            }
        }
        $initDataArray = array_filter($initDataArray);
        sort($initDataArray);

        return [$hash, implode("\n", $initDataArray)];
    }

    /**
     * @return object{
     *     id: number
     * }
     */
    public function getUser(): object
    {
        $lines = explode("\n", $this->sortedInitData);

        foreach ($lines as $line) {
            [$key, $value] = explode('=', $line, 2);
            if ($key === 'user') {
                return json_decode($value);
            }
        }
    }

    /**
     * Validate initData to ensure that it is from Telegram.
     */
    public function isSafe(): bool
    {
        $secretKey = hash_hmac('sha256', config('telegram.key'), 'WebAppData', true);
        $hash = bin2hex(hash_hmac('sha256', $this->sortedInitData, $secretKey, true));

        return strcmp($hash, $this->checksum) === 0;
    }
}
