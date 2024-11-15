<?php

namespace App\Providers;

use App\Models\Phone;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

class PhoneUserProvider implements UserProvider
{
    public function retrieveById($identifier): ?Authenticatable
    {
        $phone = Phone::find($identifier);
        return $phone?->entity;
    }

    /**
     * Не используется?
     */
    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        if (empty($credentials['phone'])) {
            return null;
        }
        $phone = Phone::auth($credentials['phone']);
        return $phone ? $phone->entity : null;
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        // Implement validation logic; for example, check if a password or token matches
        // In your case, this may involve just validating the phone number.
        return true;
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
    }

    public function retrieveByToken($identifier, $token)
    {
        // Implement if using "remember me" functionality
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        // Implement if using "remember me" functionality
    }

}
