<?php

namespace App\Models;

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

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
