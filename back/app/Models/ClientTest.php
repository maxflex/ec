<?php

namespace App\Models;

use App\Casts\JsonArrayCast;
use App\Observers\UserIdObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[ObservedBy(UserIdObserver::class)]
class ClientTest extends Model
{
    protected $fillable = [
        'name', 'minutes', 'questions', 'file',
        'year', 'client_id', 'description',
    ];

    protected $casts = [
        'file' => 'array',
        'questions' => JsonArrayCast::class,
        'answers' => JsonArrayCast::class,
    ];

    public static function getActive(int $clientId): ?ClientTest
    {
        return ClientTest::query()
            ->active()
            ->where('client_id', $clientId)
            ->first();
    }

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
        return $query->whereRaw('
            started_at is not null
            and finished_at is null
            and now() < started_at + interval `minutes` minute
        ');
    }

    public function scopeFinished($query)
    {
        $now = now();

        return $query->where(function ($query) use ($now) {
            $query->whereNotNull('started_at')
                ->where(function ($query) use ($now) {
                    $query->whereNotNull('finished_at')
                        ->orWhereRaw('started_at + interval `minutes` minute <= ?', [$now]);
                });
        });
    }

    public function getIsFinishedAttribute(): bool
    {
        $deadline = now()
            ->subMinutes($this->minutes)
            ->format('Y-m-d H:i:s');

        return $this->started_at !== null
            && ($this->finished_at !== null || $this->started_at < $deadline);
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->started_at
            && ! $this->finished_at
            && now()->lt(Carbon::parse($this->started_at)->addMinutes($this->minutes)->addMinute());
    }

    public function getIsTimedOutAttribute(): bool
    {
        return $this->is_finished && ! $this->finished_at;
    }

    public function getSecondsLeftAttribute(): ?int
    {
        if (! $this->isActive) {
            return null;
        }

        return max(
            0,
            $this->minutes * 60 - (int) abs(Carbon::parse($this->started_at)->diffInSeconds(now()))
        );
    }

    public function getResultsAttribute(): ?object
    {
        if (! $this->is_finished) {
            return null;
        }

        $result = (object) [
            'answers' => [],
            'score' => 0,
            'total' => 0,
        ];

        foreach ($this->questions as $i => $q) {
            $n = $i + 1;
            $score = 0;
            $total = 0;
            $answers = $this->answers->$n ?? [];

            foreach ($q->answers as $a) {
                $total += $q->score;
                if (in_array($a, $answers)) {
                    $score += $q->score;
                }
            }

            $result->total += $total;
            $result->score += $score;
            $result->answers[$n] = compact('score', 'total');
        }

        return $result;
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
