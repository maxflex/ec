<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Person
{
    protected $fillable = [
        'first_name', 'last_name',
        'is_active', 'is_call_notifications',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_call_notifications' => 'boolean',
    ];

    public function scopeCanLogin($query)
    {
        $query->where('is_active', true);
    }

    public function responsibleRequests(): HasMany
    {
        return $this->hasMany(Request::class, 'responsible_user_id');
    }
}
