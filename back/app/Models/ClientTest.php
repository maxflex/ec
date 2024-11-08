<?php

namespace App\Models;

use App\Casts\JsonArrayCast;
use App\Enums\Program;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class ClientTest extends Model
{
    protected $fillable = [
        'name', 'minutes', 'program', 'questions', 'file',
        'year', 'client_id'
    ];

    protected $casts = [
        'file' => 'json',
        'program' => Program::class,
        'questions' => JsonArrayCast::class,
        'answers' => JsonArrayCast::class,
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * + 1 minute – 1 минута запас на всякий случай
     */
    public function scopeActive($query)
    {
        return $query->whereRaw(<<<SQL
            started_at is not null
            and finished_at is null
            and now() < started_at + interval `minutes` + 1 minute
        SQL);
    }

    public function scopeFinished($query)
    {
        $now = now();
        return $query->where(function ($query) use ($now) {
            $query->whereNotNull('started_at')
                ->where(function ($query) use ($now) {
                    $query->whereNotNull('finished_at')
                        ->orWhereRaw('started_at + interval `minutes` + 1 minute <= ?', [$now]);
                });
        });
    }

    /**
     * TODO: use once()
     */
    public function getIsFinishedAttribute(): bool
    {
        $deadline = now()
            ->subMinutes($this->minutes)
            ->subMinute()
            ->format('Y-m-d H:i:s');
        return $this->started_at !== null
            && ($this->finished_at !== null || $this->started_at < $deadline);
    }

    /**
     * TODO: use once()
     */
    public function getIsActiveAttribute(): bool
    {
        return  $this->started_at
            && !$this->finished_at
            && now()->lt(Carbon::parse($this->started_at)->addMinutes($this->minutes)->addMinute());
    }

    public function getSecondsLeftAttribute(): int | null
    {
        if (!$this->isActive) {
            return null;
        }
        return max(
            0,
            $this->minutes * 60 - (int)abs(Carbon::parse($this->started_at)->diffInSeconds(now()))
        );
    }

    /**
     * TODO: refactor with $this->loadCount('questions')
     */
    public function getQuestionsCountAttribute(): int
    {
        return count($this->questions);
    }

    public function start()
    {
        $this->started_at = now()->format('Y-m-d H:i:s');
        $this->save();
    }

    public function finish($answers)
    {
        $this->finished_at = now()->format('Y-m-d H:i:s');
        $this->answers = $answers;
        $this->save();
    }
}
