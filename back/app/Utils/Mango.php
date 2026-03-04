<?php

namespace App\Utils;

use App\Enums\CallState;
use App\Enums\CallType;
use App\Events\CallEvent;
use App\Events\CallSummaryEvent;
use App\Http\Resources\PersonResource;
use App\Models\Call;
use App\Models\User;

class Mango
{
    const LINE_NUMBER = '74956468592';

    /**
     * Финализирует realtime-проекцию звонка:
     * 1) если запись еще активна в кеше, шлет Disconnected в realtime
     * 2) удаляет запись из кеша active calls.
     */
    private static function finalizeActiveCallByEntryId(string $entryId): void
    {
        $activeCall = cache()->tags('calls')->get($entryId);
        if ($activeCall !== null) {
            $realtimePayload = [
                ...$activeCall,
                'entry_id' => $entryId,
                'state' => CallState::disconnected->value,
            ];
            CallEvent::dispatch($realtimePayload);
        }

        cache()->tags('calls')->pull($entryId);
    }

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
            ...explode('/', $event),
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
        logger(print_r($data, true));
        switch (CallState::from($data->call_state)) {
            // новый звонок
            case CallState::appeared:
                if ($data->location === 'ivr') {
                    // Входящий звонок: номер в from.number.
                    $number = $data->from->number;
                    $params = [
                        'entry_id' => $data->entry_id,
                        'state' => $data->call_state,
                        'type' => CallType::incoming->value,
                        'user' => null,
                        'answered_at' => null,
                        'number' => $number,
                        'aon' => Call::aon($number),
                        'last_interaction' => Call::getLastInteraction($number),
                    ];
                    CallEvent::dispatch($params);
                    // Входящий без ответа: держим короткий TTL, чтобы быстрее чистить "залипшие" звонки.
                    cache()->tags('calls')->put($data->entry_id, $params, now()->addMinutes(3));
                } elseif (isset($data->from->extension)) {
                    // Outgoing started: оператор начал дозвон.
                    // Номер берем из to.number, пользователя — из from.extension.
                    $number = $data->to->number;
                    $params = [
                        'entry_id' => $data->entry_id,
                        'state' => $data->call_state,
                        'type' => CallType::outgoing->value,
                        'user' => new PersonResource(User::find($data->from->extension)),
                        'answered_at' => null,
                        'number' => $number,
                        'aon' => Call::aon($number),
                        'last_interaction' => Call::getLastInteraction($number),
                    ];
                    CallEvent::dispatch($params);
                    // Для started держим короткий TTL, дальше его продлит Connected.
                    cache()->tags('calls')->put($data->entry_id, $params, now()->addMinutes(3));
                }
                break;

                // исходящий
            case CallState::connected:
                if (isset($data->from->extension)) {
                    $number = $data->to->number;
                    // Для исходящего на этапе Connected не затираем контекст из Appeared:
                    // last_interaction и aon должны остаться теми же для стабильного UI.
                    $cachedParams = cache()->tags('calls')->get($data->entry_id);
                    $cachedParams = is_array($cachedParams) ? $cachedParams : [];
                    $params = [
                        ...$cachedParams,
                        'entry_id' => $data->entry_id,
                        'state' => $data->call_state,
                        'user' => new PersonResource(User::find($data->from->extension)),
                        'answered_at' => (new Call(['answered_at' => $data->timestamp]))->answered_at,
                        'number' => $number,
                        'aon' => $cachedParams['aon'] ?? Call::aon($number),
                        'last_interaction' => $cachedParams['last_interaction'] ?? Call::getLastInteraction($number),
                        'type' => CallType::outgoing->value,
                    ];
                } else {
                    // ответ на входящий
                    $params = cache()->tags('calls')->get($data->entry_id);
                    $params = [
                        ...$params,
                        'entry_id' => $data->entry_id,
                        'state' => $data->call_state,
                        'user' => new PersonResource(User::find($data->to->extension)),
                        'answered_at' => (new Call(['answered_at' => $data->timestamp]))->answered_at,
                        'type' => CallType::incoming->name,
                    ];
                }
                CallEvent::dispatch($params);
                // Самый длинный разговор исторически ~44 мин, поэтому safety TTL = 45 минут.
                cache()->tags('calls')->put($data->entry_id, $params, now()->addMinutes(45));
                break;

                // звонок завершён
                // 1100 Вызов завершен в нормальном режиме
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
                            'entry_id' => $data->entry_id,
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

        // Summary — финальная точка жизненного цикла звонка.
        // Здесь обязательно закрываем realtime-проекцию active calls.
        self::finalizeActiveCallByEntryId($data->entry_id);

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

    /**
     * Добавлена аудиозапись
     */
    public static function eventRecordAdded($data)
    {
        // Recording — третий эшелон защиты от "залипания" active calls:
        // если по какой-то причине Disconnected/Summary не сняли звонок,
        // событие готовности записи также принудительно завершает realtime-проекцию.
        self::finalizeActiveCallByEntryId($data->entry_id);

        Call::findOrFail($data->entry_id)->update([
            'recording' => $data->recording_id,
        ]);
    }
}
