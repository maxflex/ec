<?php

namespace App\Traits;

use App\Models\Phone;

trait HasPhones
{
    public function phones()
    {
        return $this->morphMany(Phone::class, 'entity');
    }
}
