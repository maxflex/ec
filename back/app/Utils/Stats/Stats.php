<?php

namespace App\Utils\Stats;

use Carbon\Carbon;
use Exception;

class Stats
{
    public static function getData(string $mode, string $date, int $page, array $metrics): array
    {
        $paginate = 30;

        $dateObj = Carbon::createFromFormat('Y-m-d', $date);

        // 1st page = -0 day
        $from = $dateObj->modify(sprintf('-%d %s', ($page - 1) * $paginate, $mode));
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
            $values = [];
            foreach ($metrics as $metric) {
                $metricClass = join('\\', [__NAMESPACE__, 'Metrics', $metric['metric']]);
                if (!class_exists($metricClass)) {
                    throw new Exception("Class $metricClass not found");
                }
                $values[] = $metricClass::getValue($metric['filters'], $date, $sqlFormat);
            }
            $result[] = [
                'date' => $date,
                'values' => $values
            ];
            $from->modify("-1 $mode");
        }

        return $result;
    }
}
