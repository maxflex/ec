<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestyTestCommand extends Command
{
    protected $signature = 'testy:test';

    protected $description = 'Command description';

    public function handle(): void
    {
        $signature = 'n1MCldwUe6haFdZcq8/PPPPu+AIKLRPdmewTpmQ+LoQmBrlPRuYJwuK1Ao5gO8IUOj0/MJyuSvRaYl4bgMhLV0RiR9zO61sWSX25J9E1I2EpG8qOh/Ve5Pm4qRhRDC0CfsepmyLIcb9KyoeszyTZQ3eWM4cAR1WCbJdVJeLyqW2ryGUUO2FdRZ3TvssB9LBUelT5CIhM3l9vm3F3bi6a6MrrU/jP1FPQ15gkLoIGsb9IsMqcH6XI5wdUcHK/nHw/yzT9UwvBguYMBylqPCL8L3a25wAMZi5/G1Fwnx1V92UxCS0QUr5xqLo3z19BS2gXDRVgdnwIOLdiFbUnOepTU/bR1EJYnwGvODAF5S0GjsmlliuKNr2yU1sO4xYvGSRSY0fWE5l2V2ueqIZPaT2poKeYHRQZqmtWy2daOjlPIyalmQvK+o2RsU1Nk4T6vFxgOTuXe20KLkDifKDCuRBCpmejcmk9LEk5U/VxQ5kB615gHfGEUyUJOsqqVCzS9nHgxtztyrS228wsfQ+wVq/VcsaEy/iQ1JzXdbNBzLetM7xdhWfYbHK2JyXD9h5QTFCUJFXUmGlF0IIRjD37ZwQiYXerTLxwOTUpfa/lfSQI+rHL6+PoCxQH9V1bVFho/sU5fj9MWiqjp88nJ2Ld0Swq161H9yK84eMocaErOo9as9A=';

        $response = Http::withHeaders([
            'Authorization' => $signature,
            'key-name' => '9701038111_ooo-ege-tsentr_test_28.05.2035',
            'Content-Type' => 'application/json',
        ])->withOptions([
            'verify' => storage_path('app/ssl/ca.pem'),
            'cert' => storage_path('app/ssl/mtls.crt'),
            'ssl_key' => storage_path('app/ssl/mtls.key'),
        ])
            ->post('https://217.12.103.132:2443/fsCryptoProxy');

        dd($response);
    }
}
