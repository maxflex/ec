<?php

namespace App\Models;

use App\Observers\UserIdObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

#[ObservedBy(UserIdObserver::class)]
class Pass extends Model
{
    protected $fillable = [
        'name', 'comment', 'date', 'request_id',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(Request::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function passLog(): MorphOne
    {
        return $this->morphOne(PassLog::class, 'entity');
    }

    public function getUsedAtAttribute(): ?string
    {
        return $this->passLog?->used_at;
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->used_at === null && $this->date < now()->format('Y-m-d');
    }

    /**
     * Первое использованное разрешение
     * Для фактически использованных: id === minUsedId
     * Для прогноза первого использования: date === minDate && id === minId
     */
    public function getIsFirstUsageAttribute(): bool
    {
        if (! $this->request_id) {
            return false;
        }

        $data = $this
            ->chain()
            ->leftJoin(
                'pass_logs as pl',
                fn ($join) => $join
                    ->on('pl.entity_id', '=', 'passes.id')
                    ->where('pl.entity_type', Pass::class)
            )
            ->selectRaw('
                MIN(CASE WHEN pl.id IS NOT NULL THEN passes.id ELSE NULL END) as min_used_id,
                MIN(CASE WHEN pl.id IS NULL AND passes.date >= DATE(NOW()) THEN passes.date ELSE NULL END) as min_unused_date
            ')
            ->groupBy('request_id')
            ->get()[0];

        if ($this->used_at) {
            return $this->id === $data->min_used_id;
        }

        return $this->date === $data->min_unused_date && $this->id === $this->chain()->min('id');
    }

    public function chain(): HasMany
    {
        return $this->hasMany(Pass::class, 'request_id', 'request_id');
    }
}
