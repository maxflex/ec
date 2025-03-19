<?php

namespace App\Utils\Stats\Metrics;

use App\Models\ClientLesson;
use Exception;
use Illuminate\Support\Collection;

class CalculatorMetric extends BaseMetric
{
    public function getDateField(): string
    {
        // any
        return 'created_at';
    }

    public function getBaseQuery()
    {
        // any
        return ClientLesson::query();
    }

    public function aggregate($query): int
    {
        return $query->count();
    }

    /**
     * @param  Collection<int, MetricInterface>  $metrics
     * @return float
     */
    public function getPercent(array $values, Collection $metrics)
    {
        $result = 0;
        $operator = '+';

        foreach ($this->filterValues['metrics'] as $i => $m) {
            $index = $metrics->search(fn ($e) => $e->id === $m['id']);
            if ($index === false) {
                throw new Exception('Метрика не найдена');
            }
            $value = $values[$index];

            if ($i === 0) {
                $result = $value;
            } else {
                switch ($operator) {
                    case '+':
                        $result += $value;
                        break;

                    case '-':
                        $result -= $value;
                        break;

                    case '*':
                        $result *= $value;
                        break;

                    case '/':
                        $result = $result / ($value ?: 1);
                        break;
                }
            }

            $operator = $m['operator'];
        }

        return $this->round($result);
    }

    private function round(float $number)
    {
        $round = (int) $this->filterValues['round'];

        if ($round === 0) {
            return $number;
        }

        if ($round > 0) {
            $precision = pow(10, $round);

            return round($number / $precision) * $precision;
        }

        return round($number, abs($round));
    }

    public function getValueForDate(string $date): int
    {
        return 1;
    }

    public function getTotalValue(): int
    {
        return 0;
    }
}
