<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientGroup extends Model
{
    public $timestamps = false;

    protected $fillable = ['contract_version_program_id', 'group_id'];

    public function contractVersionProgram()
    {
        return $this->belongsTo(ContractVersionProgram::class, 'contract_version_program_id', 'id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
