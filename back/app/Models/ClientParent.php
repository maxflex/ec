<?php

namespace App\Models;

use App\Contracts\CanLogin;
use App\Traits\HasTelegramMessages;
use App\Traits\RelationSyncable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

class ClientParent extends Person implements CanLogin
{
    use HasTelegramMessages, RelationSyncable, Searchable;

    public $timestamps = false;

    protected $fillable = [
        'first_name', 'last_name', 'middle_name',
        'passport', 'email',
    ];

    protected $casts = [
        'passport' => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function searchableAs()
    {
        return 'people';
    }

    public function toSearchableArray()
    {
        $class = class_basename(self::class);
        $array = $this->client->toSearchableArray();

        return [
            ...$array,
            'id' => implode('-', [$class, $this->id]),
            'first_name' => $this->first_name ? mb_strtolower($this->first_name) : '',
            'last_name' => $this->last_name ? mb_strtolower($this->last_name) : '',
            'phones' => $this->phones->pluck('number')->toArray(),
            'weight' => intval($array['weight'] / 2),
        ];
    }

    public function getLastSeenAtAttribute(): ?string
    {
        return Log::query()
            ->where('client_parent_id', $this->id)
            ->whereNull('emulation_user_id')
            ->max('created_at');
    }

    public function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query
            ->with('phones')
            ->whereHas(
                'client',
                fn ($q) => $q->whereRaw('`created_at` >= NOW() - INTERVAL 3 YEAR')
            );
    }

    public function scopeCanLogin($query)
    {
        $query->whereHas('client', fn ($q) => $q->canLogin());
    }

    public function getPassportAttribute($value)
    {
        return $value === null ? [
            'series' => null,
            'number' => null,
            'address' => null,
            'code' => null,
            'issued_date' => null,
            'issued_by' => null,
            'fact_address' => null,
        ] : json_decode($value);
    }
}
