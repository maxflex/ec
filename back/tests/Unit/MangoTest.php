<?php

namespace Tests\Unit;

use App\Http\Controllers\Pub\MangoController;
use App\Models\Call;
use App\Utils\Mango;
use Illuminate\Http\Request;

class MangoTest
{
    private $defaults = [
        [
            'entry_id' => 'MTYzNzM5MTI5MDA=x',
            'number' => '79168524317',
            'user_id' => 12,
        ],
        [
            'entry_id' => 'MTYzNzM5MTI5MDA',
            'number' => '79169113734',
            'user_id' => 5,
        ],
    ];

    /**
     * @param  0|1  $index
     */
    public function __construct(
        public int $index
    ) {}

    public function appeared()
    {
        return $this->callEvent([
            'entry_id' => $this->defaults[$this->index]['entry_id'],
            'call_id' => 'MToxMDA5Njg3Nzo1MDY6NTIyOTA1ODM4OjE=',
            'timestamp' => strtotime(now()),
            'seq' => 1,
            'call_state' => 'Appeared',
            'location' => 'ivr',
            'from' => [
                'number' => $this->defaults[$this->index]['number'],
                'line_number' => Mango::LINE_NUMBER,
            ],
            'to' => [
                'number' => Mango::LINE_NUMBER,
                'line_number' => Mango::LINE_NUMBER,
            ],
            'dct' => [
                'type' => 0,
            ],
        ]);
    }

    private function callEvent($payload)
    {
        $controller = new MangoController;
        $request = new Request([
            'json' => json_encode($payload),
        ]);

        return $controller('call', $request);
    }

    public function connected()
    {
        // ответ на звонок
        return $this->callEvent([
            'entry_id' => $this->defaults[$this->index]['entry_id'],
            'call_id' => 'MToxMDA5Njg3Nzo1MDY6NTIyOTA1ODQ2',
            'timestamp' => strtotime(now()),
            'seq' => 2,
            'call_state' => 'Connected',
            'location' => 'abonent',
            'from' => [
                'number' => $this->defaults[$this->index]['number'],
                'taken_from_call_id' => 'MToxMDA5Njg3Nzo1MDY6NTIyOTA1ODM4OjE=',
                'line_number' => Mango::LINE_NUMBER,
            ],
            'to' => [
                'extension' => $this->defaults[$this->index]['user_id'],
                'number' => 'sip:gavriluk@kapralovka.mangosip.ru',
                'line_number' => Mango::LINE_NUMBER,
                'acd_group' => '',
            ],
            'dct' => [
                'type' => 0,
            ],

        ]);
    }

    public function disconnected()
    {
        // конец соединения
        return $this->callEvent([
            'entry_id' => $this->defaults[$this->index]['entry_id'],
            'call_id' => 'MToxMDA5Njg3Nzo1MDY6NTIyOTA1ODQ2',
            'timestamp' => strtotime(now()),
            'seq' => 3,
            'call_state' => 'Disconnected',
            'location' => 'abonent',
            'from' => [
                'number' => $this->defaults[$this->index]['number'],
                'taken_from_call_id' => 'MToxMDA5Njg3Nzo1MDY6NTIyOTA1ODM4OjE=',
                'line_number' => Mango::LINE_NUMBER,
            ],
            'to' => [
                'extension' => $this->defaults[$this->index]['user_id'],
                'number' => 'sip:gavriluk@kapralovka.mangosip.ru',
                'line_number' => Mango::LINE_NUMBER,
                'acd_group' => '',
            ],
            'disconnect_reason' => 1110,
            'dct' => [
                'type' => 0,
            ],
        ]);
    }

    public function outgoingConnected()
    {
        return $this->callEvent([
            'entry_id' => 'MTYzNzM4MjI5NjM=',
            'call_id' => 'MToxMDA5Njg3NzoyMDE6MTI0MTg3Nzk3NQ==',
            'timestamp' => strtotime(now()),
            'seq' => 2,
            'call_state' => 'Connected',
            'location' => 'abonent',
            'from' => [
                'extension' => '190',
                'number' => 'sip:axpll@kapralovka.mangosip.ru',
                'line_number' => '74956461080',
            ],
            'to' => [
                'number' => '79653155538',
            ],
            'dct' => [
                'type' => 0,
            ],

        ]);
    }

    public function outgoingDisconnected()
    {
        return $this->callEvent([
            'entry_id' => 'MTYzNzM4MjI5NjM=',
            'call_id' => 'MToxMDA5Njg3NzoyMDE6MTI0MTg3Nzk3NQ==',
            'timestamp' => 1672053704,
            'seq' => 3,
            'call_state' => 'Disconnected',
            'location' => 'abonent',
            'from' => [
                'extension' => '190',
                'number' => 'sip:axpll@kapralovka.mangosip.ru',
                'line_number' => '74956461080',
            ],
            'to' => [
                'number' => '79653155538',
            ],
            'disconnect_reason' => 1120,
            'dct' => [
                'type' => 0,
            ],
        ]);
    }

    public function summary()
    {
        Call::query()->where('id', 'MjE0NTM3MDU1Mzg=')->delete();
        $payload = [
            'entry_id' => 'MjE0NTM3MDU1Mzg=',
            'call_direction' => 1,
            'from' => [
                'number' => '79959201122',
            ],
            'to' => [
                'extension' => '140',
                'number' => 'sip:volkova2301@kapralovka.mangosip.ru',
            ],
            'line_number' => '74956468592',
            'create_time' => 1724782003,
            'forward_time' => 0,
            'talk_time' => 0,
            'end_time' => 1724782012,
            'entry_result' => 0,
            'disconnect_reason' => 1110,
        ];
        $controller = new MangoController;
        $request = new Request([
            'json' => json_encode($payload),
        ]);

        return $controller('summary', $request);
    }
}
