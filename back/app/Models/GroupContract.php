<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupContract extends Model
{
    public $timestamps = false;

    protected $fillable = ['group_id', 'contract_id'];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}
