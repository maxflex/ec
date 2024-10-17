<?php

namespace App\Models;

use App\Enums\{RequestDirection, RequestStatus};
use App\Traits\{HasComments, HasPhones, RelationSyncable};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Request extends Model
{
    use HasPhones, HasComments, RelationSyncable;

    protected $fillable = [
        'responsible_user_id', 'direction', 'status', 'client_id',
        'is_verified'
    ];

    protected $casts = [
        'is_verified' => 'bool',
        'status' => RequestStatus::class,
        'direction' => RequestDirection::class,
    ];

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function passes(): HasMany
    {
        return $this->hasMany(Pass::class);
    }


    /**
     * @param bool $excludeSelected исключить выбранного клиента (нужно для списка заявок)
     * @return Client[]
     */
    public function getClients(bool $excludeSelected = false)
    {
        $numbers = $this->phones()->pluck('number');

        $query = Client::where(fn($q) => $q
            ->whereHas('phones', fn($q) => $q->whereIn('number', $numbers))
            ->orWhereHas('parent.phones', fn($q) => $q->whereIn('number', $numbers))
        );

        if ($this->client_id) {
            $excludeSelected
                ? $query->where('id', '<>', $this->client_id)
                : $query->orWhere('id', $this->client_id);
        }

        return $query->get()->all();
    }

    public static function booted()
    {
        self::creating(function ($request) {
            $request->ip = request()->ip();
        });
    }
}
