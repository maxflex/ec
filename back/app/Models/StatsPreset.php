<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatsPreset extends Model
{
    protected $fillable = [
        'label'
    ];

    protected $casts = [
        'filters' => 'array',
    ];
}
