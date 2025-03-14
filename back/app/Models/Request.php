<?php

namespace App\Models;

use App\Enums\Direction;
use App\Enums\RequestStatus;
use App\Traits\HasComments;
use App\Traits\HasPhones;
use App\Traits\IsSearchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Request extends Model
{
    use HasComments, HasPhones, IsSearchable;

    protected $fillable = [
        'responsible_user_id', 'direction', 'status', 'client_id',
        'is_verified', 'yandex_id', 'google_id', 'source',
    ];

    protected $casts = [
        'is_verified' => 'bool',
        'status' => RequestStatus::class,
        'direction' => Direction::class,
    ];

    public static function booted()
    {
        self::creating(function ($request) {
            $request->ip = request()->ip();
        });
    }

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
     * @return Collection<int, Request>
     *
     * TODO: remove $clients param after once()
     */
    public function getAssociatedRequests()
    {
        $numbers = $this->phones->pluck('number');
        $clientIds = $this->getAssociatedClients()->pluck('id');
        if ($this->client_id) {
            $clientIds->push($this->client_id);
        }

        return Request::where('id', '<>', $this->id)
            ->withCount('comments')
            ->where(fn ($q) => $q->whereHas('phones', fn ($q) => $q->whereIn('number', $numbers))
                ->when($clientIds->count(), fn ($q) => $q->orWhereIn('client_id', $clientIds))
            )
            ->get();
    }

    /**
     * @return Collection<int, Client>
     *
     * TODO: use once()
     */
    public function getAssociatedClients()
    {
        $numbers = $this->phones->pluck('number');

        $query = Phone::whereIn('number', $numbers)
            ->whereIn('entity_type', [
                Client::class,
                ClientParent::class,
            ])
            ->with('entity');

        if ($this->client_id) {
            $condition = sprintf(
                "(entity_type = '%s' AND entity_id = %d)",
                addslashes(Client::class),
                $this->client_id
            );
            $query->orWhereRaw($condition);
        }

        $clients = collect();
        foreach ($query->get() as $phone) {
            $clients->push(
                $phone->entity_type === Client::class ? $phone->entity : $phone->entity->client
            );
        }

        return $clients->unique('id');
    }

    public function getSearchWeight(): int
    {
        return 100;
    }

    public function getSearchIsActive(): bool
    {
        return false;
    }

    public function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query
            ->with('phones')
            ->whereRaw('`created_at` >= NOW() - INTERVAL 2 YEAR');
    }
}
