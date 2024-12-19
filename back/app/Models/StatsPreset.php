<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatsPreset extends Model
{
    protected $fillable = [
        'metric', 'color', 'label', 'filters'
    ];

    protected $casts = [
        'filters' => 'array',
    ];
}
