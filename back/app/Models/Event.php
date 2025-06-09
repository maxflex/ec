<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Event extends Model
{
    protected $fillable = [
        'date', 'time', 'name', 'description',
        'is_afterclass', 'duration', 'year',
        'is_private', 'file',
    ];

    protected $casts = [
        'file' => 'array',
        'is_afterclass' => 'boolean',
        'is_private' => 'boolean',
        'duration' => 'int',
    ];

    public function participants(): HasMany
    {
        return $this->hasMany(EventParticipant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function telegramLists(): HasMany
    {
        return $this->hasMany(TelegramList::class);
    }

    public function getTimeEndAttribute()
    {
        if (! $this->duration) {
            return null;
        }

        return Carbon::parse($this->date.' '.$this->time)
            ->addMinutes($this->duration)
            ->format('H:i');
    }
}
