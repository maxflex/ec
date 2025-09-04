<?php

namespace App\Traits;

use App\Models\Client;
use App\Models\Representative;
use App\Models\Teacher;

trait HasName
{
    /**
     * Форматирование имени по умолчанию
     */
    public function formatNameDefault()
    {
        return match (get_class($this)) {
            Client::class => $this->first_name,
            Representative::class => $this->formatName('first-middle'),
            Teacher::class => $this->formatName('initials'),
            default => $this->formatName(),
        };
    }

    /**
     * @param  'last-first'|'initials'|'full'|'first-middle'  $format
     */
    public function formatName(string $format = 'last-first'): string
    {
        $name = match ($format) {
            'full' => [
                $this->last_name,
                $this->first_name,
                $this->middle_name,
            ],

            'initials' => [
                $this->last_name,
                mb_substr($this->first_name, 0, 1).'.',
                mb_substr($this->middle_name, 0, 1).'.',
            ],

            'first-middle' => [
                $this->first_name,
                $this->middle_name,
            ],

            default => [
                $this->last_name,
                $this->first_name,
            ],
        };

        return implode(' ', $name);
    }
}
