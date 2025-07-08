<?php

namespace App\Models;

use App\Casts\Timestamp;
use App\Enums\CallType;
use App\Http\Resources\CallAppAonResource;
use App\Http\Resources\CallAppLastInteractionResource;
use App\Utils\Phone as UtilsPhone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Call extends Model
{
    const DISABLE_LOGS = true;

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'type' => CallType::class,
        'created_at' => Timestamp::class,
        'answered_at' => Timestamp::class,
        'finished_at' => Timestamp::class,
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
     * АОН – автоматический определитель номера.
     * Определяем модель по номеру телефона
     */
    public static function aon(string $number): ?CallAppAonResource
    {
        // Кто звонит?
        $phone = Phone::where('number', $number)
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
                ClientParent::class,
                Teacher::class,
                Request::class,
            ])
            ->latest('id')
            ->first();

        return $phone ? new CallAppAonResource($phone) : null;
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
     * После пропущенного должен быть контакт более 10 сек
     */
    public function getIsMissedCallbackAttribute(): bool
    {
        return $this->is_missed
            && $this->chain()
                ->answered()
                ->where('created_at', '>', $this->created_at)
                ->whereRaw('TIMESTAMPDIFF(second, created_at, answered_at) > 10')
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
        return $query->whereNotNull('answered_at');
    }
}
