<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ContractGroup extends Pivot
{
    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
