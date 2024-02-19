<?php

namespace App\Models;

use App\Enums\Program;
use Illuminate\Database\Eloquent\Model;

class ContractProgram extends Model
{
    protected $casts = [
        'program' => Program::class
    ];
}
