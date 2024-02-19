<?php

namespace App\Models;

use App\Enums\Program;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $casts = [
        'program' => Program::class,
        'zoom' => 'array'
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
