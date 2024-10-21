<?php

namespace App\Contracts;

interface CanLogin
{
    public function scopeCanLogin($query);
}