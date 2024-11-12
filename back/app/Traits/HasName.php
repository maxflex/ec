<?php

namespace App\Traits;

trait HasName
{
    /**
     * @param 'full'|'initials'|'first-middle'|'last-first' $format
     * @return string
     */
    public function formatName(string $format = 'last-first'): string
    {
        return match ($format) {
            'full' => join(' ', [
                $this->last_name,
                $this->first_name,
                $this->middle_name,
            ]),
            'initials' => join(' ', [
                $this->last_name,
                mb_substr($this->first_name, 0, 1) . '.',
                mb_substr($this->middle_name, 0, 1) . '.',
            ]),
            'first-middle' => join(' ', [
                $this->first_name,
                $this->middle_name,
            ]),
            default => join(' ', [
                $this->last_name,
                $this->first_name,
            ]),
        };
    }
}