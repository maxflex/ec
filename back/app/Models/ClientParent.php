<?php

namespace App\Models;

use App\Contracts\CanLogin;
use App\Traits\{HasName, HasPhones, HasTelegramMessages, RelationSyncable};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

class ClientParent extends Model implements CanLogin
{
    use HasName, HasPhones, RelationSyncable, Searchable, HasTelegramMessages;

    public $timestamps = false;

    protected $fillable = [
        'first_name', 'last_name', 'middle_name', 'passport_series', 'passport_number',
        'passport_address', 'passport_code', 'passport_issued_date', 'passport_issued_by',
        'fact_address'
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
            'first_name' => $this->first_name ?? '',
            'last_name' => $this->last_name ?? '',
            'middle_name' => $this->middle_name ?? '',
            'phones' => $this->phones()->pluck('number'),
            'weight' => intval($array['weight'] / 2)
        ];
    }

    public function scopeCanLogin($query)
    {
        $query->whereHas('client', fn($q) => $q->canLogin());
    }
}
