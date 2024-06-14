<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    public $incrementing = false;
    public $timestamps = false;

    protected $keyType = 'string';

    public static function booted()
    {
        static::creating(function ($photo) {
            $photo->id = uniqid();
        });
    }
}
