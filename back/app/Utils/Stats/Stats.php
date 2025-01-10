<?php

namespace App\Utils\Stats;

use Carbon\Carbon;
use Exception;

class Stats
{
    /**
     * @return array{data: array, is_last_page: bool}
     */
    public static function getData(
        string $mode,
        string $date,
        string $dateFrom,
        array  $metrics,
        ?int   $page,
    ): array
    {
        $dateObj = Carbon::createFromFormat('Y-m-d', $date);
        $dateFromObj = Carbon::createFromFormat('Y-m-d', $dateFrom);

        /**
         * Если $page не указан, то без пагинации от $dateFrom до $date
         * (нужно для экспорта)
         */
        if ($page === null) {
            $from = $dateObj->copy();
            $to = Carbon::createFromFormat('Y-m-d', $dateFrom);
            $isLastPage = true;
        } else {
            // 1st page = -0 day
            $paginate = 30;
            $from = $dateObj->modify(sprintf('-%d %s', ($page - 1) * $paginate, $mode));
            $to = (clone $from)->modify(sprintf("-%d %s", $paginate - 1, $mode));
            $isLastPage = $to->lessThan($dateFromObj);
        }

        if ($mode === 'week') {
            $from->startOfWeek();
        }

        switch ($mode) {
            case 'day':
                $format = 'Y-m-d';
                $sqlFormat = '%Y-%m-%d';
                break;
            case 'week':
                $format = 'Y-m-d';
                $sqlFormat = '%Y-%u';
                break;
            case 'month':
                $format = 'Y-m-01';
                $sqlFormat = '%Y-%m-01';
                break;
//            case 'year':
            default:
                $format = 'Y-01-01';
                $dateFromObj->startOf('year');
                $sqlFormat = '%Y-01-01';
                break;
        }

        /**
         * from : "2025-06-14"
         * to : "2025-04-26"
         */
        $data = [];
        while ($from->gte($to) && $from->gte($dateFromObj)) {
            $date = $from->format($format);
            $values = [];
            foreach ($metrics as $metric) {
                $metricClass = join('\\', [__NAMESPACE__, 'Metrics', $metric['metric']]);
                if (!class_exists($metricClass)) {
                    throw new Exception("Class $metricClass not found");
                }
                $values[] = $metricClass::getValue($metric['filters'], $date, $dateFrom, $sqlFormat, $mode);
            }
            $data[] = [
                'date' => $date,
                'values' => $values
            ];
            $from->modify("-1 $mode");
        }

        return [
            'data' => $data,
            'is_last_page' => $isLastPage
        ];
    }

    public static function getTotals(
        string $date,
        string $dateFrom,
        array  $metrics
    ): array
    {
        $values = [];
        foreach ($metrics as $metric) {
            $metricClass = join('\\', [__NAMESPACE__, 'Metrics', $metric['metric']]);
            if (!class_exists($metricClass)) {
                throw new Exception("Class $metricClass not found");
            }
            $values[] = $metricClass::getTotals($metric['filters'], $date, $dateFrom);
        }

        return $values;
    }
}
