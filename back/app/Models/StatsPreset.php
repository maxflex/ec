<?php

namespace App\Models;

use App\Observers\UserIdObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(UserIdObserver::class)]
class StatsPreset extends Model
{
    protected $fillable = [
        'name', 'params',
    ];

    protected $casts = [
        'params' => 'array',
    ];
}
