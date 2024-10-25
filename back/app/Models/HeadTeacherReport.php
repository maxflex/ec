<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HeadTeacherReport extends Model
{
    protected $fillable = [
        'year', 'month', 'text'
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public static function booted()
    {
        static::addGlobalScope(fn($query) => $query->orderByRaw("
            FIELD(month, 9,10,11,12,1,2,3,4,5)
        "));
    }
}
