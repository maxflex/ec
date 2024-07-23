<?php

namespace App\Models;

use App\Utils\Phone as UtilsPhone;
use App\Utils\Session;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Phone extends Model implements Authenticatable
{
    public $timestamps = false;
    protected $fillable = ['number', 'comment'];

    // public function getNumberAttribute($value)
    // {
    //     return $value ? UtilsPhone::format($value) : null;
    // }

    public function getAuthIdentifier()
    {
        return $this->entity_id;
    }
    public function getAuthIdentifierName()
    {
    }
    public function getAuthPassword()
    {
    }
    public function getRememberToken()
    {
    }
    public function setRememberToken($value)
    {
    }
    public function getRememberTokenName()
    {
    }

    public function entity()
    {
        return $this->morphTo();
    }

    public function telegramMessages()
    {
        return $this->hasMany(TelegramMessage::class);
    }

    public function scopeWhereNumber($query, $number)
    {
        return $query->where('number', UtilsPhone::clean($number));
    }

    public function scopeWithTelegram($query)
    {
        return $query->whereNotNull('telegram_id');
    }

    public static function auth($number): ?Phone
    {
        $phones = Phone::query()
            ->whereIn('entity_type', [
                User::class,
                Client::class,
                ClientParent::class,
                Teacher::class,
            ])
            ->whereNumber($number)
            ->get();
        if ($phones->count() !== 1) {
            return null;
        }
        return $phones->first();
    }

    public function createSessionToken(): string
    {
        return Session::createToken($this);
    }

    public static function booted()
    {
        static::saving(fn ($phone) => $phone->number = UtilsPhone::clean($phone->number));
    }
}
