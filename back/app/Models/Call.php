<?php

namespace App\Models;

use App\Casts\Timestamp;
use App\Contracts\HasMenuCount;
use App\Enums\{CallType};
use App\Utils\Mango;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Call extends Model implements HasMenuCount
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
     * TODO: переделать
     */
    public function phonee()
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

    public function getPhoneAttribute(): ?Phone
    {
        return Mango::aon($this->number);
    }

    // перезвоны
    public function callbacks()
    {
        return $this->hasMany(Call::class, 'number', 'number')
            ->where('type', CallType::outgoing)
            ->whereRaw(<<<SQL
                created_at > calls.created_at
                AND created_at < calls.created_at + INTERVAL 1 MONTH
            SQL
            );
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

    public static function getMenuCount(): int
    {
        return Call::missed()->count();
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
