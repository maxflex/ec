<?php

namespace App\Models;

use App\Enums\TeacherStatus;
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

        // кандидаты к логину
        $candidates = [];
        foreach ($phones as $phone) {
            $entity = $phone->entity;
            switch ($phone->entity_type) {
                case User::class:
                    if ($entity->is_active) {
                        $candidates[] = $phone;
                    }
                    break;

                case Teacher::class:
                    if ($entity->status === TeacherStatus::active) {
                        $candidates[] = $phone;
                    }
                    break;

                // намеренно нет break
                case ClientParent::class:
                    $entity = $entity->client;

                case Client::class:
                    if ($entity->contracts()->where('year', 2024)->exists()) {
                        $candidates[] = $phone;
                    }
            }

            if (count($candidates) !== 1) {
                return null;
            }

            return $candidates[0];
        }

        return $phones->first();
    }

    public function isAdmin(): bool
    {
        return $this->entity_type === User::class;
    }

    public function isTeacher(): bool
    {
        return $this->entity_type === Teacher::class;
    }

    public function isClient(): bool
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
