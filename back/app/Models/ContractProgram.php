<?php

namespace App\Models;

use App\Enums\Program;
use Illuminate\Database\Eloquent\Model;

class ContractProgram extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'program', 'lessons', 'lessons_planned', 'price', 'is_closed'
    ];

    protected $casts = [
        'program' => Program::class,
        'lessons' => 'integer',
        'lessons_planned' => 'integer',
        'price' => 'integer'
    ];
}
