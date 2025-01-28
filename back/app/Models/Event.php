<?php

namespace App\Models;

use App\Traits\RelationSyncable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Event extends Model
{
    use RelationSyncable;

    protected $fillable = [
        'date', 'time', 'name', 'description',
        'is_afterclass', 'duration', 'year',
    ];

    protected $casts = [
        'is_afterclass' => 'boolean',
    ];

    public function participants(): HasMany
    {
        return $this->hasMany(EventParticipant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTimeEndAttribute()
    {
        if (! $this->duration) {
            return null;
        }

        return Carbon::parse($this->date.' '.$this->time)
            ->addMinutes(intval($this->duration))
            ->format('H:i');
    }
}
