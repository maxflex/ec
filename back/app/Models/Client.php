<?php

namespace App\Models;

use App\Traits\HasPhones;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasPhones;

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }
}
