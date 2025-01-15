<?php

namespace App\Utils;

use App\Enums\CallState;
use App\Enums\CallType;
use App\Events\CallEvent;
use App\Events\CallSummaryEvent;
use App\Http\Resources\CallAppPhoneResource;
use App\Http\Resources\PersonResource;
use App\Models\Call;
use App\Models\Phone;
use App\Models\Request;
use App\Models\User;

class Mango
{
    const LINE_NUMBER = '74956468592';

    public static function handle($event, string $json)
    {
        $data = json_decode($json);

//        logger('MANGO', compact('event', 'data'));

        /**
         * "call"          => eventCall
         * "summary"       => eventSummary
         * "record/added"  => eventRecordAdded
         */
        $fn = str()->camel(implode('-', [
            'event',
            ...explode('/', $event)
        ]));

        if (method_exists(self::class, $fn)) {
            self::$fn($data);
        }
    }

    /**
     * Контроль событий в режиме реального времени
     */
    public static function eventCall($data)
    {
        if ($data->from->line_number !== self::LINE_NUMBER) {
            return;
        }
        switch (CallState::from($data->call_state)) {
            // новый звонок
            case CallState::appeared:
                // обрабатываем только входящие, исходящие игнорируем
                if ($data->location === 'ivr') {
                    $phone = self::aon($data->from->number);
                    $params = [
                        'state' => $data->call_state,
                        'type' => CallType::incoming->value,
                        'user' => null,
                        'answered_at' => null,
                        'number' => $data->from->number,
                        'phone' => $phone === null ? null : new CallAppPhoneResource($phone),
                    ];
                    CallEvent::dispatch($params);
                    cache()->tags('calls')->put($data->entry_id, $params, now()->addMinutes(10));
                }
                break;

            // исходящий
            case CallState::connected:
                if (isset($data->from->extension)) {
                    $phone = self::aon($data->to->number);
                    $params = [
                        'state' => $data->call_state,
                        'user' => new PersonResource(User::find($data->from->extension)),
                        'answered_at' => (new Call(['answered_at' => $data->timestamp]))->answered_at,
                        'number' => $data->to->number,
                        'phone' => $phone === null ? null : new CallAppPhoneResource($phone),
                        'type' => CallType::outgoing->name,
                    ];
                } else {
                    // ответ на входящий
                    $params = cache()->tags('calls')->get($data->entry_id);
                    $params = [
                        ...$params,
                        'state' => $data->call_state,
                        'user' => new PersonResource(User::find($data->to->extension)),
                        'answered_at' => (new Call(['answered_at' => $data->timestamp]))->answered_at,
                        'type' => CallType::incoming->name,
                    ];
                }
                CallEvent::dispatch($params);
                cache()->tags('calls')->put($data->entry_id, $params, now()->addHour());
                break;

            // звонок завершён
            // 1110 Вызов завершен вызывающим абонентом
            // 1120 Вызов завершен вызываемым абонентом
            // 1163 Превышено время ожидания в очереди удержания
            case CallState::disconnected:
                if (in_array($data->disconnect_reason, [1110, 1120, 1163])) {
                    $params = cache()->tags('calls')->get($data->entry_id);
                    // если на входящий не ответили, disconnected-1110 посылается всем
                    // поэтому, может быть null после обработки первого
                    if ($params !== null) {
                        $params = [
                            ...$params,
                            'state' => $data->call_state,
                        ];
                        CallEvent::dispatch($params);
                        cache()->tags('calls')->pull($data->entry_id);
                    }
                }
                break;

            case CallState::onHold:
                //
        }
    }

    public static function eventSummary($data)
    {
        if ($data->line_number !== self::LINE_NUMBER) {
            return;
        }
        if ($data->call_direction === 1) {
            $type = CallType::incoming;
            $userId = $data->talk_time > 0 ? $data->to->extension : null;
            $number = $data->from->number;
        } else {
            $type = CallType::outgoing;
            $userId = $data->from->extension;
            $number = $data->to->number;
        }

        if (is_localhost()) {
            Call::whereId($data->entry_id)->delete();
        }

        $call = Call::create([
            'id' => $data->entry_id,
            'user_id' => $userId,
            'type' => $type->name,
            'number' => $number,
            'line' => $data->line_number,
            'created_at' => $data->create_time,
            'answered_at' => $data->talk_time,
            'finished_at' => $data->end_time,
        ]);

        event(new CallSummaryEvent($call));
    }

    public static function eventRecordAdded($data)
    {
        Call::whereId($data->entry_id)->update([
            'recording' => $data->recording_id
        ]);
    }

    /**
     * АОН – автоматический определитель номера
     * Определяем модель по номеру телефона
     */
    public static function aon(string $number): ?Phone
    {
        return Phone::where('number', $number)
            ->where('entity_type', '<>', Request::class)
            ->where('entity_type', '<>', User::class)
            ->first();
    }
}