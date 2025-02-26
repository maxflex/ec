<?php

namespace App\Traits;

use App\Models\Phone;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

trait HasPhones
{
    public function getPhoneNumbers(): Collection
    {
        return $this->phones()->pluck('number');
    }

    public function phones(): MorphMany
    {
        return $this->morphMany(Phone::class, 'entity');
    }

    /**
     * https://typesense.org/docs/guide/tips-for-searching-common-types-of-data.html#phone-numbers
     * Кладет все телефоны в формате подходящем для поиска.
     * На примере 79252727210:
     *  9252727210,
     *  2727210,
     *  925,
     *  7210,
     *
     * @return string[]
     */
    public function phonesToSearchIndex(): array
    {
        return $this->phones
            ->pluck('number')
            ->map(function ($number) {
                $str = str($number)->substr(1);

                return [
                    $str->value(),                           // 9252727210
                    $str->substr(0, 3)->value(), // 925
                    $str->substr(3)->value(),           // 2727210
                    $str->substr(6)->value(),           // 7210
                ];
            })
            ->flatten()
            ->all();
    }
}
