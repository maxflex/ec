<?php

namespace App\Models;

use App\Contracts\CanLogin;
use App\Traits\HasPhones;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

abstract class Person extends Authenticatable implements CanLogin
{
    use HasPhones;

    const DISABLE_LOGS = true;

    public function photo(): MorphOne
    {
        return $this->morphOne(Photo::class, 'entity');
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo?->url;
    }

    public function getLastSeenAtAttribute(): ?string
    {
        return $this->logs()->whereNull('emulation_user_id')->max('created_at');
    }

    public function logs(): MorphMany
    {
        return $this->morphMany(Log::class, 'entity');
    }

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
