<?php

namespace App\Utils\Stats\Metrics;

use App\Models\ClientLesson;

class PercentMetric extends BaseMetric
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

    public function getPercent(array $values)
    {
        [$denominator, $numerator] = $values;
        $denominator = $denominator ?: 1;

        // Вычисление процента
        $percent = $numerator / $denominator;
        $roundValue = (int) $this->filterValues['round'];

        // Определение количества знаков после запятой
        $decimalPlaces = 6; // по умолчанию 6 знаков после запятой

        if ($roundValue > 0 && $percent > 1) {
            $decimalPlaces = $roundValue; // если положительное число, то округляем к целому
        } elseif ($roundValue < 0) {
            $decimalPlaces = abs($roundValue); // если отрицательное, срезаем до положительного числа
        }

        // Округление или срезание
        return round($percent, $decimalPlaces);
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
