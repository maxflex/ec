<?php

namespace App\Utils\Stats;

use Exception;

class Stats
{
    public static function getData(string $mode, int $page, array $items): array
    {
        $paginate = 30;

        // 1st page = -0 day
        $from = now()->modify(sprintf('-%d %s', ($page - 1) * $paginate, $mode));
        if ($mode === 'week') {
            $from->startOfWeek();
        }
        $to = (clone $from)->modify(sprintf("-%d %s", $paginate - 1, $mode));


        switch ($mode) {
            case 'day':
                $format = 'Y-m-d';
                $sqlFormat = '%Y-%m-%d';
                break;
            // case 'week':
            //     $format = 'Y-m-d';
            //     $sqlFormat = 'LEFT(DATE_ADD(%1$s, INTERVAL(-WEEKDAY(%1$s)) DAY), 10)';
            //     break;
            case 'month':
                $format = 'Y-m-01';
                $sqlFormat = '%Y-%m-01';
                break;
//            case 'year':
            default:
                $format = 'Y-01-01';
                $sqlFormat = '%Y-01-01';
                break;
        }

        /**
         * from : "2024-06-14"
         * to : "2024-04-26"
         */
        $result = [];
        while ($from >= $to) {
            $date = $from->format($format);
            $metrics = [];
            foreach ($items as $item) {
                $item = (object)$item;
                $metricClass = join('\\', [__NAMESPACE__, 'Metrics', $item->metric]);
                if (!class_exists($metricClass)) {
                    throw new Exception("Class $metricClass not found");
                }
                $metrics[] = $metricClass::run($item->filters, $date, $sqlFormat);
            }
            $result[] = [
                'date' => $date,
                'metrics' => $metrics
            ];
            $from->modify("-1 $mode");
        }

        return $result;
    }
}
