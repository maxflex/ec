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
     *
     * TODO: use once()
     */
    public function getClients(bool $excludeSelected = false)
    {
        $numbers = $this->phones->pluck('number');

        $query = Phone::whereIn('number', $numbers)
            ->whereIn('entity_type', [
                Client::class,
                ClientParent::class
            ])
            ->with('entity');

        if ($this->client_id) {
            $condition = sprintf(
                "(entity_type = '%s' AND entity_id = %d)",
                addslashes(Client::class),
                $this->client_id
            );
            $excludeSelected
                ? $query->whereRaw("NOT $condition")
                : $query->orWhereRaw($condition);
        }

        $clients = [];
        foreach ($query->get() as $phone) {
            $clients[] = $phone->entity_type === Client::class ? $phone->entity : $phone->entity->client;
        }

        return $clients;
    }

    /**
     * @params ?Client[] $clients
     * @return Request[]
     *
     * TODO: remove $clients param after once()
     */
    public function getAssociatedRequests(?array $clients = null)
    {
        $numbers = $this->phones->pluck('number');
        $clientIds = collect($clients ?? $this->getClients())->pluck('id');
        if ($this->client_id) {
            $clientIds->push($this->client_id);
        }

        return Request::where('id', '<>', $this->id)
            ->where(fn($q) => $q->whereHas('phones', fn($q) => $q->whereIn('number', $numbers))
                ->when($clientIds->count(), fn($q) => $q->orWhereIn('client_id', $clientIds))
            )
            ->get()->all();
    }

    public static function booted()
    {
        self::creating(function ($request) {
            $request->ip = request()->ip();
        });
    }
}
