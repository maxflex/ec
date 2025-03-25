<?php

namespace App\Utils\Stats\Metrics;

use App\Models\Pass;
use Illuminate\Support\Facades\DB;

class RequestPassesMetric extends BaseMetric
{
    protected $filters = [
        'direction' => ['direction'],
        'hasUsed' => ['has_used'],
    ];

    public function getDateField(): string
    {
        return '`date`';
    }

    public function aggregate($query): int
    {
        return $query->count();
    }

    public function getBaseQuery()
    {
        $cte = Pass::query()
            ->whereNotNull('request_id')
            ->leftJoin('pass_logs as pl', fn ($q) => $q
                ->on('pl.entity_id', '=', 'passes.id')
                ->where('pl.entity_type', '=', Pass::class)
            )
            ->selectRaw('
                passes.date,
                passes.request_id,
                COUNT(pl.id) as `passes_used`
            ')
            ->groupByRaw('passes.date, passes.request_id');

        return DB::table('x')->withExpression('x', $cte);
    }

    protected function filterHasUsed(&$query, $value)
    {
        $query->where('passes_used', $value ? '>' : '=', 0);
    }

    protected function filterDirection(&$query, array $directions)
    {
        if (count($directions) === 0) {
            return;
        }

        $query
            ->join('requests as r', 'r.id', '=', 'x.request_id')
            ->whereIn('r.direction', $directions);
    }
}
