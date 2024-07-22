<?php

namespace App\Traits;

trait HasName
{
    public function formatName()
    {
        return join(' ', [
            $this->last_name,
            $this->first_name,
        ]);
    }

    public function formatNameFull()
    {
        return join(' ', [
            $this->last_name,
            $this->first_name,
            $this->middle_name,
        ]);
    }

    public function formatNameInitials()
    {
        return join(' ', [
            $this->last_name,
            mb_substr($this->first_name, 0, 1) . '.',
            mb_substr($this->middle_name, 0, 1) . '.',
        ]);
    }

    public function formatNameFirstMiddle()
    {
        return join(' ', [
            $this->first_name,
            $this->middle_name,
        ]);
    }
}
