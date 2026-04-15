<?php

namespace App\Utils\Stats\Metrics;

use App\Models\Call;
use Illuminate\Database\Eloquent\Builder;

class CallMetric extends BaseMetric
{
    protected $filters = [
        'findInSet' => ['user_id'],
        'equals' => ['type'],
        'multiple' => ['caller_type'],
        'callDuration' => ['call_duration'],
        'null' => ['answered_at'],
    ];

    public function getDateField(): string
    {
        return 'created_at';
    }

    public function getBaseQuery()
    {
        return Call::query();
    }

    public function aggregate($query): int
    {
        return $query->count();
    }

    /**
     * Фильтрация диапазонов длительности разговора для метрики "Звонки" в /stats.
     * Логика синхронизирована с /calls.
     */
    protected function filterCallDuration(Builder $query, array $durations): void
    {
        $durations = array_values(array_unique(array_filter(
            $durations,
            fn ($value) => is_string($value) && in_array($value, [
                'no_conversation',
                'very_short',
                'short',
                'medium',
                'long',
                'very_long',
            ], true),
        )));

        if ($durations === []) {
            return;
        }

        $query->where(function (Builder $durationQuery) use ($durations): void {
            foreach ($durations as $i => $duration) {
                $whereMethod = $i === 0 ? 'where' : 'orWhere';

                $durationQuery->{$whereMethod}(function (Builder $singleDurationQuery) use ($duration): void {
                    match ($duration) {
                        'no_conversation' => $singleDurationQuery->whereNull('answered_at'),
                        'very_short' => $this->applyDurationRangeFilter($singleDurationQuery, null, 10),
                        'short' => $this->applyDurationRangeFilter($singleDurationQuery, 10, 60),
                        'medium' => $this->applyDurationRangeFilter($singleDurationQuery, 60, 300),
                        'long' => $this->applyDurationRangeFilter($singleDurationQuery, 300, 600),
                        'very_long' => $this->applyDurationRangeFilter($singleDurationQuery, 600, null),
                        default => null,
                    };
                });
            }
        });
    }

    /**
     * Общий фильтр диапазона по длительности в секундах.
     */
    private function applyDurationRangeFilter(
        Builder $query,
        ?int $minSeconds,
        ?int $maxSeconds,
    ): void {
        $query
            ->whereNotNull('answered_at')
            ->whereNotNull('finished_at');

        if ($minSeconds !== null) {
            $query->whereRaw('TIMESTAMPDIFF(SECOND, answered_at, finished_at) > ?', [$minSeconds]);
        }

        if ($maxSeconds !== null) {
            $query->whereRaw('TIMESTAMPDIFF(SECOND, answered_at, finished_at) <= ?', [$maxSeconds]);
        }
    }
}
