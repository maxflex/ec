<?php

namespace App\Utils\Stats;

use App\Utils\Stats\Metrics\MetricInterface;
use Carbon\Carbon;
use Exception;

class Stats
{
    /**
     * @return array{
     *  data: array,
     *  is_last_page: bool,
     *  totals: array
     * }
     */
    public static function get(
        string $mode,
        string $dateFromStr,
        string $dateToStr,
        array $metricsData,
        ?int $page,
    ): array {
        $dateFrom = Carbon::createFromFormat('Y-m-d', $dateFromStr);
        $dateTo = Carbon::createFromFormat('Y-m-d', $dateToStr);

        // Если $page не указан, то без пагинации (нужно для экспорта)
        if ($page === null) {
            $windowCurrentDate = $dateFrom->copy();
            $windowTo = $dateTo->copy();
        } else {
            $paginate = 30;
            $windowTo = $dateTo->copy()->sub($mode, ($page - 1) * $paginate);
            $windowCurrentDate = $windowTo->copy()->sub($mode, $paginate - 1);
        }

        if ($mode === 'week') {
            $windowCurrentDate->startOfWeek();
        }

        if (is_localhost()) {
            logger('dateFrom: '.$dateFrom->format('Y-m-d'));
            logger('dateTo: '.$dateTo->format('Y-m-d'));
            logger('windowCurrentDate: '.$windowCurrentDate->format('Y-m-d'));
            logger('windowTo: '.$windowTo->format('Y-m-d'));

        }

        $isLastPage = $windowCurrentDate->lte($dateFrom);

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
                $dateFrom->startOf('year');
                $sqlFormat = '%Y-01-01';
                break;
        }

        /** @var array<int, MetricInterface> $metrics */
        $metrics = [];

        foreach ($metricsData as $m) {
            $metricClass = implode('\\', [__NAMESPACE__, 'Metrics', $m['metric']]);
            if (! class_exists($metricClass)) {
                throw new Exception("Class $metricClass not found");
            }
            $metricInstance = new $metricClass(
                $m['filters'],
                $dateFromStr,
                $dateToStr,
                $sqlFormat,
                $mode
            );
            if (! $metricInstance instanceof MetricInterface) {
                throw new Exception("Class $metricClass does not implement MetricInterface");
            }
            $metrics[] = $metricInstance;
        }

        /**
         * from : "2025-04-26"
         * to : "2025-06-14"
         */
        $data = [];
        while ($windowCurrentDate->lte($windowTo) && $windowCurrentDate->lte($dateTo)) {
            $dateStr = $windowCurrentDate->format($format);
            $values = [];
            foreach ($metrics as $metric) {
                $values[] = $metric->getValueForDate($dateStr);
            }
            $data[] = [
                'date' => $dateStr,
                'values' => $values,
            ];
            $windowCurrentDate->modify("+1 $mode");
        }

        $result = [
            'data' => array_reverse($data),
            'is_last_page' => $isLastPage,
        ];

        if ($page === 1) {
            $totals = [];
            foreach ($metrics as $metric) {
                $totals[] = $metric->getTotalValue();
            }
            $result['totals'] = $totals;
        }

        return $result;
    }
}
