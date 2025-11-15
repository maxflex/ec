<?php

namespace App\Models;

use App\Observers\UserIdObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

#[ObservedBy(UserIdObserver::class)]
class Event extends Model
{
    protected $fillable = [
        'date', 'time', 'name', 'description',
        'duration', 'year', 'file',
    ];

    protected $casts = [
        'file' => 'array',
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
