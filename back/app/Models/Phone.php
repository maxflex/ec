<?php

namespace App\Models;

use App\Utils\Phone as UtilsPhone;
use Illuminate\Database\Eloquent\Model;

class Phone extends Model
{
    public $timestamps = false;
    protected $fillable = ['number', 'comment'];

    public function getNumberAttribute($value)
    {
        return $value ? UtilsPhone::format($value) : null;
    }

    public function entity()
    {
        return $this->morphTo();
    }

    public function scopeWhereNumber($query, $number)
    {
        return $query->where('number', UtilsPhone::clean($number));
    }

    public static function auth($number): ?Phone
    {
        $phones = Phone::query()
            ->whereIn('entity_type', [
                User::class,
                Client::class,
                Teacher::class,
            ])
            ->whereNumber($number)
            ->get();
        if ($phones->count() !== 1) {
            return null;
        }
        return $phones->first();
    }

    public static function booted()
    {
        static::saving(fn ($phone) => $phone->number = UtilsPhone::clean($phone->number));
    }
}
