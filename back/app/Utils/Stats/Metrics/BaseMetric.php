<?php

namespace App\Utils\Stats\Metrics;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class BaseMetric extends Controller implements MetricInterface
{
    public static function getValue(array $filters, string $date, string $sqlFormat, string $mode): int
    {
        $request = new Request($filters);
        $dateField = static::getDateField();
        $query = static::getQuery();

        if ($mode === 'week') {
            $query->whereRaw("DATE_FORMAT(`$dateField`, ?) = DATE_FORMAT(?, ?)", [
                $sqlFormat,
                $date,
                $sqlFormat,
            ]);
        } else {
            $query->whereRaw("DATE_FORMAT(`$dateField`, ?) = ?", [
                $sqlFormat,
                $date,
            ]);
        }

        $controller = new static();
        $controller->filter($request, $query);
        return static::getQueryValue($query);
    }
}
