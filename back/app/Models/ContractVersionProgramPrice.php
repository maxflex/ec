<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractVersionProgramPrice extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'lessons', 'price', 'contract_version_program_id'
    ];
}
