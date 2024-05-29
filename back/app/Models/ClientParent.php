<?php

namespace App\Models;

use App\Traits\HasPhones;
use App\Traits\RelationSyncable;
use Illuminate\Database\Eloquent\Model;

class ClientParent extends Model
{
    use HasPhones, RelationSyncable;

    public $timestamps = false;

    protected $fillable = [
        'first_name', 'last_name', 'middle_name', 'passport_series', 'passport_number',
        'passport_address', 'passport_code', 'passport_issued_date', 'passport_issued_by',
        'fact_address'
    ];
}
