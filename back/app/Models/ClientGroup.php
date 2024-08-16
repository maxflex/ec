<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientGroup extends Model
{
    public $timestamps = false;

    protected $fillable = ['group_id', 'contract_id'];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
