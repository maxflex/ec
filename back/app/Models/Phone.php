<?php

namespace App\Models;

use App\Observers\PhoneObserver;
use App\Utils\Phone as UtilsPhone;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[ObservedBy(PhoneObserver::class)]
class Phone extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'number', 'comment', 'telegram_id', 'is_telegram_disabled',
    ];

    protected $casts = [
        'is_telegram_disabled' => 'bool',
    ];

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
                fn ($phone) => $phone->entity_type::whereId($phone->entity_id)->canLogin()->exists()
            );

        // должен быть в итоге единственный кандидат к логину
        return $candidates->count() === 1 ? $candidates->first() : null;
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
}
