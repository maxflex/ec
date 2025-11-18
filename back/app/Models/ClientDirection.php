<?php

namespace App\Models;

use App\Enums\CvpStatus;
use App\Enums\Direction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientDirection extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'year', 'direction',
    ];

    protected $casts = [
        'direction' => Direction::class,
    ];

    // отображение направлений всегда идёт со статусом
    protected $appends = [
        'status',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Если есть хотя бы 1 exceeded, то exceeded
     * Иначе если есть хоты бы 1 active, то active
     * Иначе finished
     */
    public function getStatusAttribute(): CvpStatus
    {
        $contracts = Contract::query()
            ->with('versions', fn ($q) => $q
                ->where('is_active', true)
                ->with('programs')
            )
            ->where([
                'year' => $this->year,
                'client_id' => $this->client_id,
            ])->get();

        $hasActive = false;
        foreach ($contracts as $contract) {
            foreach ($contract->active_version->programs as $program) {
                if ($program->status === CvpStatus::exceeded) {
                    return CvpStatus::exceeded;
                }
                if ($program->status === CvpStatus::active) {
                    $hasActive = true;
                }
            }
        }

        return $hasActive ? CvpStatus::active : CvpStatus::finished;
    }
}
