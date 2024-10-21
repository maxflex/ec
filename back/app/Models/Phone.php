<?php

namespace App\Models;

use App\Utils\Phone as UtilsPhone;
use App\Utils\Session;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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

    // @phpstan-ignore return.missing
    public function getAuthIdentifierName()
    {
    }

    // @phpstan-ignore return.missing
    public function getAuthPassword()
    {
    }

    // @phpstan-ignore return.missing
    public function getRememberToken()
    {
    }

    public function setRememberToken($value)
    {
    }

    // @phpstan-ignore return.missing
    public function getRememberTokenName()
    {
    }

    public function entity(): MorphTo
    {
        return $this->morphTo();
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
        $candidates = Phone::query()
            ->whereIn('entity_type', [
                User::class,
                Client::class,
                ClientParent::class,
                Teacher::class,
            ])
            ->whereNumber($number)
            ->get()
            ->filter(
                fn($phone) => $phone->entity_type::whereId($phone->entity_id)->canLogin()->exists()
            );

        // должен быть в итоге единственный кандидат к логину
        return $candidates->count() === 1 ? $candidates->first() : null;
    }

    public function getIsAdminAttribute(): bool
    {
        return $this->entity_type === User::class;
    }

    public function getIsTeacherAttribute(): bool
    {
        return $this->entity_type === Teacher::class;
    }

    public function getIsClientAttribute(): bool
    {
        return in_array($this->entity_type, [
            Client::class,
            ClientParent::class
        ]);
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
