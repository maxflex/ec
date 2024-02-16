<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractVersion extends Model
{
    public function subjects()
    {
        return $this->hasMany(ContractSubject::class);
    }

    public function payments()
    {
        return $this->hasMany(ContractPayment::class);
    }
}
