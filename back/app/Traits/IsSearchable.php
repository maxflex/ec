<?php

namespace App\Traits;

use Laravel\Scout\Searchable;

trait IsSearchable
{
    use Searchable;

    public function searchableAs()
    {
        return 'people';
    }

    public function toSearchableArray()
    {
        return [
            'id' => implode('-', [class_basename(self::class), $this->id]),
            'first_name' => $this->hasAttribute('first_name') && $this->first_name ? mb_strtolower($this->first_name) : '',
            'last_name' => $this->hasAttribute('last_name') && $this->last_name ? mb_strtolower($this->last_name) : '',
            'phones' => $this->getSearchPhones(),
            'is_active' => $this->getSearchIsActive(),
            'weight' => $this->getSearchWeight(),
        ];
    }

    public function getSearchPhones(): array
    {
        return $this->phones->pluck('number')->toArray();
    }

    public function getSearchIsActive(): bool
    {
        return self::canLogin()->whereId($this->id)->exists();
    }

    public function getSearchWeight(): int
    {
        return 100;
    }
}
