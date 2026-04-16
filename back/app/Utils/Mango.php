<?php

namespace App\Utils;

use App\Enums\CallState;
use App\Enums\CallType;
use App\Events\CallEvent;
use App\Events\MenuCountsUpdatedEvent;
use App\Http\Resources\PersonResource;
use App\Jobs\SyncCallRecordingToStorageJob;
use App\Models\Call;
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

        // logger(print_r($data, true));
        $entryId = $data->entry_id;
        switch (CallState::from($data->call_state)) {
            // новый звонок
            case CallState::appeared:
                if ($data->location === 'ivr') {
                    // Входящий звонок: номер в from.number.
                    $number = $data->from->number;
                    $params = [
                        'entry_id' => $entryId,
                        'state' => $data->call_state,
                        'type' => CallType::incoming->value,
                        'user' => null,
                        'answered_at' => null,
                        'number' => $number,
                        'aon' => Call::aon($number),
                        'last_interaction' => Call::getLastInteraction($number),
                    ];

                    // исторически ответ на входящий через 5 мин максимум
                    self::updateRealtimeState($entryId, $params, 5);
                } elseif (isset($data->from->extension)) {
                    // Outgoing started: оператор начал дозвон.
                    // Номер берем из to.number, пользователя — из from.extension.
                    $number = $data->to->number;
                    $params = [
                        'entry_id' => $entryId,
                        'state' => $data->call_state,
                        'type' => CallType::outgoing->value,
                        'user' => new PersonResource(User::find($data->from->extension)),
                        'answered_at' => null,
                        'number' => $number,
                        'aon' => Call::aon($number),
                        'last_interaction' => Call::getLastInteraction($number),
                    ];

                    // исторически ответ на исходящий через 1 мин максимум
                    self::updateRealtimeState($entryId, $params, 3);
                }

                break;

            case CallState::connected:
                // Не затираем контекст из Appeared: last_interaction и aon должны остаться теми же для стабильного UI.
                $params = cache()->tags('calls')->get($entryId) ?? [];

                // исходящий приняли
                if (isset($data->from->extension)) {
                    $number = $data->to->number;
                    $params = [
                        ...$params,
                        'entry_id' => $entryId,
                        'state' => $data->call_state,
                        'user' => new PersonResource(User::find($data->from->extension)),
                        'answered_at' => (new Call(['answered_at' => $data->timestamp]))->answered_at,
                        'number' => $number,
                        'aon' => $params['aon'] ?? Call::aon($number),
                        'last_interaction' => $params['last_interaction'] ?? Call::getLastInteraction($number),
                        'type' => CallType::outgoing->value,
                    ];
                } else {
                    // ответ на входящий
                    $params = [
                        ...$params,
                        'entry_id' => $entryId,
                        'state' => $data->call_state,
                        'user' => new PersonResource(User::find($data->to->extension)),
                        'answered_at' => (new Call(['answered_at' => $data->timestamp]))->answered_at,
                        'type' => CallType::incoming->name,
                    ];
                }

                // Самый длинный разговор исторически ~44 мин, поэтому safety TTL = 45 минут.
                self::updateRealtimeState($entryId, $params, 45);

                break;

                // звонок завершён
                // 1100 Вызов завершен в нормальном режиме
                // 1110 Вызов завершен вызывающим абонентом
                // 1120 Вызов завершен вызываемым абонентом
                // 1163 Превышено время ожидания в очереди удержания
            case CallState::disconnected:
                if (in_array($data->disconnect_reason, [1110, 1120, 1163])) {
                    $params = cache()->tags('calls')->get($entryId);
                    // если на входящий не ответили, disconnected-1110 посылается всем
                    // поэтому, может быть null после обработки первого
                    if ($params !== null) {
                        self::disconnectRealtimeState($entryId);
                    }
                }
                break;

            case CallState::onHold:
                //
        }
    }

    /**
     * Canonical realtime-state по звонку.
     * В кэше хранится последняя известная фаза звонка по entry_id.
     */
    private static function updateRealtimeState(string $entryId, array $params, int $ttlMinutes): void
    {
        CallEvent::dispatch($params);
        cache()->tags('calls')->put($entryId, $params, now()->addMinutes($ttlMinutes));
    }

    /**
     * Отправляет Disconnected-сигнал в SSE, очищает ключ по $entryId.
     * Отправляется в 3 случаях (несколько эшелонов доставки на всякий случай):
     * когда звонок завершается, в Summary и в Recording
     */
    public static function disconnectRealtimeState(string $entryId)
    {
        cache()->tags('calls')->pull($entryId);
        CallEvent::dispatch([
            'entry_id' => $entryId,
            'state' => CallState::disconnected->value,
        ]);
    }

    public static function eventSummary($data)
    {
        if ($data->line_number !== self::LINE_NUMBER) {
            return;
        }

        $entryId = $data->entry_id;

        // Summary — финальная точка жизненного цикла звонка.
        // Здесь обязательно закрываем realtime-проекцию active calls.
        self::disconnectRealtimeState($entryId);

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
            // На localhost можем повторно прогонять одинаковый entry_id из тестового UI.
            // Чистим старую запись по Mango entry_id, а не по внутреннему PK.
            Call::query()->where('entry_id', $entryId)->delete();
        }

        Call::create([
            'entry_id' => $entryId,
            'user_id' => $userId,
            'type' => $type->name,
            'number' => $number,
            'line' => $data->line_number,
            'created_at' => $data->create_time,
            'answered_at' => $data->talk_time,
            'finished_at' => $data->end_time,
        ]);

        // Count пропущенных звонков пересчитывается на фронте только после Summary.
        MenuCountsUpdatedEvent::dispatchCallsCount();
    }

    /**
     * Добавлена аудиозапись
     */
    public static function eventRecordAdded($data)
    {
        $entryId = $data->entry_id;
        $recordingId = trim((string) ($data->recording_id ?? ''));

        // Recording — третий эшелон защиты от "залипания" active calls:
        // если по какой-то причине Disconnected/Summary не сняли звонок,
        // событие готовности записи также принудительно завершает realtime-проекцию.
        self::disconnectRealtimeState($entryId);

        // recording_id в calls больше не храним:
        // передаем его в очередь только как входные данные для скачивания файла в S3.
        if ($recordingId !== '') {
            SyncCallRecordingToStorageJob::dispatch((string) $entryId, $recordingId);
        }
    }

    /**
     * Строит подписанную ссылку Mango для скачивания/прослушивания записи.
     */
    public static function buildRecordingLink(string $recordingId, string $action = 'download'): string
    {
        $timestamp = now()->addMinutes(5)->unix();
        // sign=sha256(vpbx_api_key + timestamp + recording_id + vpbx_api_salt)
        $sign = hash('sha256', implode('', [
            config('mango.api_key'),
            $timestamp,
            $recordingId,
            config('mango.api_salt'),
        ]));

        return implode('/', [
            'https://app.mango-office.ru/vpbx/queries/recording/link',
            $recordingId,
            $action,
            config('mango.api_key'),
            $timestamp,
            $sign,
        ]);
    }
}
