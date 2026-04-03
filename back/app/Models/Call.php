<?php

namespace App\Models;

use App\Casts\Timestamp;
use App\Enums\CallerType;
use App\Enums\CallType;
use App\Http\Resources\CallAppAonResource;
use App\Http\Resources\CallAppLastInteractionResource;
use App\Observers\CallObserver;
use App\Utils\Phone as UtilsPhone;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

#[ObservedBy(CallObserver::class)]
class Call extends Model
{
    const DISABLE_LOGS = true;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'type' => CallType::class,
        'caller_type' => CallerType::class,
        'created_at' => Timestamp::class,
        'answered_at' => Timestamp::class,
        'finished_at' => Timestamp::class,
        'instruction' => 'array',
    ];

    /**
     * Получить активные разговоры
     * Те, которые разговаривают прямо сейчас
     */
    public static function getActive()
    {
        $keys = cache()->connection()
            ->zrange(cache()->getPrefix().'tag:calls:entries', 0, -1);
        $keys = collect($keys)->map(fn ($key) => collect(explode(':', $key))->last());

        return $keys
            ->map(fn ($key) => cache()->tags('calls')->get($key))
            ->filter(fn ($item) => $item !== null)
            ->values();
    }

    /**
     * Количество звонков для счетчика в админ-меню.
     * Считаем только необработанные пропущенные в окне:
     * [19:00 вчера, 19:00 сегодня).
     */
    public static function getMenuCount(): int
    {
        $from = Carbon::yesterday()->setTime(19, 0);
        $to = Carbon::today()->setTime(19, 0);

        return self::query()
            ->missedNoCallback()
            ->where('created_at', '>=', $from)
            ->where('created_at', '<', $to)
            ->count();
    }

    /**
     * АОН – автоматический определитель номера.
     * Определяем модель по номеру телефона
     */
    public static function aon(string $number): ?CallAppAonResource
    {
        $phone = self::aonPhone($number);

        return $phone ? new CallAppAonResource($phone) : null;
    }

    /**
     * Исходные данные для АОН по номеру.
     * Используем единый алгоритм и в UI, и в логике suggest-комментариев.
     */
    public static function aonPhone(string $number): ?Phone
    {
        // Кто звонит?
        return Phone::where('number', $number)
            ->where('entity_type', '<>', User::class)
            ->orderByRaw('
                CASE
                    WHEN ENTITY_TYPE = ? THEN 4
                    WHEN ENTITY_TYPE = ? THEN 3
                    WHEN ENTITY_TYPE = ? THEN 2
                    WHEN ENTITY_TYPE = ? THEN 1
                END DESC
            ', [
                Client::class,
                Representative::class,
                Teacher::class,
                Request::class,
            ])
            ->latest('id')
            ->first();
    }

    /**
     * Когда было последнее взаимодействие с номером (отображается при входящем)
     */
    public static function getLastInteraction(string $number): ?CallAppLastInteractionResource
    {
        $call = Call::where('number', UtilsPhone::clean($number))->latest()->first();

        return $call ? new CallAppLastInteractionResource($call) : null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Используется при поиске в whereHas('phone')
     */
    public function phone(): HasOne
    {
        return $this->hasOne(Phone::class, 'number', 'number');
    }

    /**
     * Пропущенный
     */
    public function getIsMissedAttribute(): bool
    {
        return $this->type === CallType::incoming && $this->answered_at === null;
    }

    /**
     * Пропущенный, но после этого был контакт
     * После пропущенного должен быть:
     * - любой последующий отвеченный контакт, или
     * - любая последующая исходящая попытка перезвона.
     */
    public function getIsMissedCallbackAttribute(): bool
    {
        return $this->is_missed
            && $this->chain()
                ->where('created_at', '>', $this->created_at)
                ->where(function ($query) {
                    $query
                        ->whereNotNull('answered_at')
                        // Перезвон считаем обработкой пропущенного даже без ответа клиента.
                        ->orWhere('type', CallType::outgoing->value);
                })
                ->exists();
    }

    /**
     * Цепочка звонков по номеру телефона
     */
    public function chain(): HasMany
    {
        return $this->hasMany(Call::class, 'number', 'number');
    }

    public function getHasRecordingAttribute(): bool
    {
        return $this->recording !== null;
    }

    /**
     * Длительность разговора в секундах.
     * Считается от момента ответа до завершения звонка.
     */
    public function getDurationAttribute(): int
    {
        // Для пропущенных звонков answered_at может быть null.
        if (! $this->answered_at || ! $this->finished_at) {
            return 0;
        }

        $answeredAt = Carbon::parse($this->answered_at);
        $finishedAt = Carbon::parse($this->finished_at);

        return (int) $answeredAt->diffInSeconds($finishedAt);
    }

    public function getIsIncomingAttribute(): bool
    {
        return $this->type === CallType::incoming;
    }

    /**
     * $action = download / play
     */
    public function getRecording($action)
    {
        $timestamp = now()->addMinutes(5)->unix();
        // sign=sha256(vpbx_api_key + timestamp + recording_id + vpbx_api_salt)
        $sign = hash('sha256', implode('', [
            config('mango.api_key'),
            $timestamp,
            $this->recording,
            config('mango.api_salt'),
        ]));

        return implode('/', [
            'https://app.mango-office.ru/vpbx/queries/recording/link',
            $this->recording,
            $action,
            config('mango.api_key'),
            $timestamp,
            $sign,
        ]);
    }

    /**
     * Отвеченные звонки
     * Те, где состоялся разговор
     */
    public function scopeAnswered($query)
    {
        $query->whereNotNull('answered_at');
    }

    /**
     * Пропущенные без перезвона:
     * входящий звонок, по которому не было ответа,
     * и в этой же цепочке не появилось:
     * - последующего отвеченного контакта;
     * - последующей исходящей попытки перезвона.
     */
    public function scopeMissedNoCallback($query): void
    {
        $this->applyMissedScope($query, withCallback: false);
    }

    /**
     * Базовая логика для "пропущенных" фильтров.
     */
    private function applyMissedScope($query, bool $withCallback): void
    {
        $query
            ->where('type', CallType::incoming->value)
            ->whereNull('answered_at');

        $whereMethod = $withCallback ? 'whereExists' : 'whereNotExists';

        $query->{$whereMethod}(function ($subQuery) {
            $subQuery
                ->selectRaw('1')
                ->from('calls as callback_calls')
                ->whereColumn('callback_calls.number', 'calls.number')
                ->whereColumn('callback_calls.created_at', '>', 'calls.created_at')
                ->where(function ($callbackQuery) {
                    $callbackQuery
                        // Успешный контакт по номеру.
                        ->whereNotNull('callback_calls.answered_at')
                        // Или сам факт исходящего дозвона (даже без ответа).
                        ->orWhere('callback_calls.type', CallType::outgoing->value);
                });
        });
    }

    /**
     * Пропущенные с перезвоном:
     * входящий звонок, по которому не было ответа,
     * но позже в цепочке появился отвеченный контакт
     * или была исходящая попытка дозвона.
     */
    public function scopeMissedWithCallback($query): void
    {
        $this->applyMissedScope($query, withCallback: true);
    }
}
