<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractVersionPayment extends Model
{
    public $timestamps = false;
    protected $fillable = ['sum', 'date'];
}
