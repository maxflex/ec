<?php

namespace App\Traits;

use App\Models\Phone;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

/**
 * Номера телефона есть ещё у заявки, поэтому нельзя вынести в IsPerson
 */
trait HasPhones
{
    public function getPhoneNumbers(): Collection
    {
        return $this->phones()->pluck('number');
    }

    /**
     * @return MorphMany<Phone>
     */
    public function phones(): MorphMany
    {
        return $this->morphMany(Phone::class, 'entity');
    }
}
