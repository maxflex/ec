<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstructionSign extends Model
{
    public $timestamps = false;

    protected $fillable = ['teacher_id'];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public static function booted()
    {
        static::creating(function ($sign) {
            $sign->signed_at = now();
        });
    }
}
