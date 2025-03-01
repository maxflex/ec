<?php

namespace App\Contracts;

interface Person
{
    // человек может логиниться
    public function scopeCanLogin($query);
}
