<?php

namespace App\Models;

use App\Traits\IsSearchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Representative extends Person
{
    use IsSearchable;

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

    public function getSearchWeight(): int
    {
        return $this->client->getSearchWeight() / 2;
    }

    public function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query
            ->with('phones')
            ->whereHas(
                'client',
                fn ($q) => $q->whereRaw('`created_at` >= NOW() - INTERVAL 5 YEAR')
            );
    }

    /**
     * Для отображения пункта меню "оценки"
     */
    public function getHasGradesAttribute(): bool
    {
        return $this->client->has_grades;
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
