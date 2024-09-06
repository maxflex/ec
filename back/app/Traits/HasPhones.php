<?php

namespace App\Traits;

use App\Models\Phone;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasPhones
{
    public function phones(): MorphMany
    {
        return $this->morphMany(Phone::class, 'entity');
    }
}
