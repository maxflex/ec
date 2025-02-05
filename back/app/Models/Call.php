<?php

namespace App\Models;

use App\Casts\Timestamp;
use App\Http\Resources\CallAppAonResource;
use App\Enums\{CallType};
use App\Utils\Mango;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public function getIsMissedAttribute(): bool
    {
        return $this->type === CallType::incoming && $this->answered_at === null;
    }

    public function getIsMissedCallbackAttribute(): bool
    {
        return $this->is_missed && $this->callbacks()->exists();
    }

    public function getHasRecordingAttribute(): bool
    {
        return $this->recording !== null;
    }

    // перезвоны
    public function callbacks()
    {
        // mega hack
        $createdAt = $this->created_at ? "'" . $this->created_at . "'" : 'laravel_reserved_0.created_at';

        return $this->hasMany(Call::class, 'number', 'number')
            ->where('type', CallType::outgoing->name)
            ->whereRaw("calls.created_at > $createdAt")
            ->whereRaw("calls.created_at < $createdAt + INTERVAL 1 MONTH");
    }

    /**
     * Входящие звонки без ответа за месяц
     * + по ним не было перезвона в течение месяца с момента звонка
     */
    public function scopeMissed($query)
    {
//        $keys = cache()->connection()
//            ->zrange(cache()->getPrefix() . "tag:missed:entries", 0, -1);
//        $hiddenIds = collect($keys)->map(fn($key) => collect(explode(":", $key))->last());
        return $query
//            ->whereNotIn('id', $hiddenIds)
            ->where('type', CallType::incoming)
            ->whereNull('answered_at')
            ->whereRaw('created_at > NOW() - INTERVAL 1 MONTH')
            ->whereDoesntHave('callbacks');
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
            config('mango.api_salt')
        ]));
        return implode('/', [
            'https://app.mango-office.ru/vpbx/queries/recording/link',
            $this->recording,
            $action,
            config('mango.api_key'),
            $timestamp,
            $sign
        ]);
    }

    public static function getActive()
    {
        $keys = cache()->connection()
            ->zrange(cache()->getPrefix() . "tag:calls:entries", 0, -1);
        $keys = collect($keys)->map(fn($key) => collect(explode(":", $key))->last());
        return $keys
            ->map(fn($key) => cache()->tags('calls')->get($key))
            ->filter(fn($item) => $item !== null)
            ->values();
    }

    public function hide()
    {
        cache()->tags('missed')->put($this->id, 1, now()->addMonth());
    }


    /**
     * АОН – автоматический определитель номера.
     * Определяем модель по номеру телефона
     */
    public static function aon(string $number): CallAppAonResource
    {
        // Кто звонит?
         $phone = Phone::where('number', $number)
            ->where('entity_type', '<>', User::class)
            ->orderByRaw("
                CASE
                    WHEN ENTITY_TYPE = ? THEN 4
                    WHEN ENTITY_TYPE = ? THEN 3
                    WHEN ENTITY_TYPE = ? THEN 2
                    WHEN ENTITY_TYPE = ? THEN 1
                END DESC
            ", [
                Client::class,
                ClientParent::class,
                Teacher::class,
                Request::class,
            ])
            ->latest('id')
            ->first();

         return new CallAppAonResource($phone);
    }

//    public static function getCounts()
//    {
//        return Call::missed()->count();
//    }
//
//    public static function booted()
//    {
//        static::created(fn () => CountsUpdated::dispatch(self::class));
//    }
}
