<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatsPreset extends Model
{
    protected $fillable = [
        'name', 'params'
    ];

    protected $casts = [
        'params' => 'array',
    ];
}
