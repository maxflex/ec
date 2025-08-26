<?php

namespace App\Models;

use App\Contracts\CanLogin;
use App\Traits\HasName;
use App\Traits\HasPhones;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

abstract class Person extends Authenticatable implements CanLogin
{
    use HasName, HasPhones;

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

    /**
     * У типа User не используется
     */
    public function telegramMessages(): MorphMany
    {
        return $this->morphMany(TelegramMessage::class, 'entity');
    }
}
