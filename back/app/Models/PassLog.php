<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PassLog extends Model
{
    public $timestamps = false;

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }
}
