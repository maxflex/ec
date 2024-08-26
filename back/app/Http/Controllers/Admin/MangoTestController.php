<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Common\MangoController;
use App\Http\Controllers\Controller;
use App\Utils\Mango;
use Illuminate\Http\Request;

/**
 * Тестирование звонилки, localhost only
 */
class MangoTestController extends Controller
{
    protected $defaults = [
        [
            "entry_id" => "MTYzNzM5MTI5MDA=x",
            "number" => "79168524317",
            "user_id" => 12,
        ],
        [
            "entry_id" => "MTYzNzM5MTI5MDA",
            "number" => "79252727210",
            "user_id" => 5,
        ]
    ];

    public function __invoke($event)
    {
        if (method_exists($this, $event)) {
            return $this->$event();
        }

        abort(404, "Event $event not found");
    }

    private function appeared()
    {
        return $this->callEvent([
            "entry_id" => $this->defaults[request()->input('index')]['entry_id'],
            "call_id" => "MToxMDA5Njg3Nzo1MDY6NTIyOTA1ODM4OjE=",
            "timestamp" => strtotime(now()),
            "seq" => 1,
            "call_state" => "Appeared",
            "location" => "ivr",
            "from" => [
                "number" => $this->defaults[request()->input('index')]['number'],
                "line_number" => Mango::LINE_NUMBER
            ],
            "to" => [
                "number" => Mango::LINE_NUMBER,
                "line_number" => Mango::LINE_NUMBER
            ],
            "dct" => [
                "type" => 0
            ]
        ]);
    }

    private function connected()
    {
        // ответ на звонок
        return $this->callEvent([
            "entry_id" => $this->defaults[request()->input('index')]['entry_id'],
            "call_id" => "MToxMDA5Njg3Nzo1MDY6NTIyOTA1ODQ2",
            "timestamp" => strtotime(now()),
            "seq" => 2,
            "call_state" => "Connected",
            "location" => "abonent",
            "from" => [
                "number" => $this->defaults[request()->input('index')]['number'],
                "taken_from_call_id" => "MToxMDA5Njg3Nzo1MDY6NTIyOTA1ODM4OjE=",
                "line_number" => Mango::LINE_NUMBER
            ],
            "to" => [
                "extension" => $this->defaults[request()->input('index')]['user_id'],
                "number" => "sip:gavriluk@kapralovka.mangosip.ru",
                "line_number" => Mango::LINE_NUMBER,
                "acd_group" => ""
            ],
            "dct" => [
                "type" => 0
            ]

        ]);
    }

    private function disconnected()
    {
        // конец соединения
        return $this->callEvent([
            "entry_id" => $this->defaults[request()->input('index')]['entry_id'],
            "call_id" => "MToxMDA5Njg3Nzo1MDY6NTIyOTA1ODQ2",
            "timestamp" => strtotime(now()),
            "seq" => 3,
            "call_state" => "Disconnected",
            "location" => "abonent",
            "from" => [
                "number" => $this->defaults[request()->input('index')]['number'],
                "taken_from_call_id" => "MToxMDA5Njg3Nzo1MDY6NTIyOTA1ODM4OjE=",
                "line_number" => Mango::LINE_NUMBER
            ],
            "to" => [
                "extension" => $this->defaults[request()->input('index')]['user_id'],
                "number" => "sip:gavriluk@kapralovka.mangosip.ru",
                "line_number" => Mango::LINE_NUMBER,
                "acd_group" => ""
            ],
            "disconnect_reason" => 1110,
            "dct" => [
                "type" => 0
            ]
        ]);
    }

    private function outgoingConnected()
    {
        return $this->callEvent([
            'entry_id' => 'MTYzNzM4MjI5NjM=',
            'call_id' => 'MToxMDA5Njg3NzoyMDE6MTI0MTg3Nzk3NQ==',
            "timestamp" => strtotime(now()),
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

    private function outgoingDisconnected()
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

    private function callEvent($payload)
    {
        $controller = new MangoController();
        $request = new Request([
            'json' => json_encode($payload)
        ]);
        return $controller('call', $request);
    }
}
