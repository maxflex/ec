<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractVersion extends Model
{
    public function programs()
    {
        return $this->hasMany(ContractProgram::class);
    }

    public function payments()
    {
        return $this->hasMany(ContractPayment::class);
    }
}
