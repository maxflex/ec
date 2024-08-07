<?php

namespace App\Utils\Stats\Metrics;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class BaseMetric extends Controller implements MetricInterface
{
    public static function run(array $filters, string $date, string $sqlFormat): int
    {
        $request = new Request($filters);
        $query = static::getQuery($date, $sqlFormat);
        $controller = new static();
        $controller->filter($request, $query);
        return static::getMetric($query);
    }
}
