<?php

namespace App\Traits;

use App\Models\Phone;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

trait HasPhones
{
    public function phones(): MorphMany
    {
        return $this->morphMany(Phone::class, 'entity');
    }

    public function getPhoneNumbers(): Collection
    {
        return $this->phones()->pluck('number');
    }
}
