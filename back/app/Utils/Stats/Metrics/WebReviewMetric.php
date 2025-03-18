<?php

namespace App\Utils\Stats\Metrics;

use App\Models\WebReview;

class WebReviewMetric extends BaseMetric
{
    public function getDateField(): string
    {
        return 'created_at';
    }

    public function aggregate($query): int
    {
        return $query->count();
    }

    public function getBaseQuery()
    {
        return WebReview::query();
    }
}
