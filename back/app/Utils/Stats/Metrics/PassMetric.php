<?php

namespace App\Utils\Stats\Metrics;

use App\Models\Client;
use App\Models\Pass;
use App\Models\PassLog;
use App\Models\Representative;
use App\Models\Teacher;
use App\Models\User;

class PassMetric extends BaseMetric
{
    protected $filters = [
        'type' => ['type'],
        'direction' => ['direction'],
    ];

    public function getDateField(): string
    {
        return 'used_at';
    }

    public function aggregate($query): int
    {
        return $query->count();
    }

    public function getBaseQuery()
    {
        return PassLog::query();
    }

    protected function filterType($query, array $types)
    {
        $query->where(function ($query) use ($types) {
            foreach ($types as $type) {
                $query->orWhere(function ($query) use ($type) {
                    switch ($type) {
                        case 'noRequest':
                            // разовый без заявки
                            $query
                                ->where('entity_type', Pass::class)
                                ->whereHas('pass', fn ($q) => $q->whereNull('request_id'));
                            break;

                        case 'hasRequest':
                            // разовый по заявке
                            $query
                                ->where('entity_type', Pass::class)
                                ->whereHas('pass', fn ($q) => $q->whereNotNull('request_id'));
                            break;

                        case 'client':
                            $query->where('entity_type', Client::class);
                            break;

                        case 'representative':
                            $query->where('entity_type', Representative::class);
                            break;

                        case 'teacher':
                            $query->where('entity_type', Teacher::class);
                            break;

                        case 'user':
                            $query->where('entity_type', User::class);
                            break;
                    }
                });
            }
        });
    }

    protected function filterDirection($query, array $directions)
    {
        if (count($directions) === 0) {
            return;
        }

        $query->whereHas('pass',
            fn ($q) => $q->whereHas(
                'request',
                fn ($q) => $q->whereIn('direction', $directions)
            )
        );
    }
}
