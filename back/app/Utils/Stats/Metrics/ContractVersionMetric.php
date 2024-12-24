<?php

namespace App\Utils\Stats\Metrics;

use App\Enums\Direction;
use App\Models\ContractVersion;
use Illuminate\Database\Eloquent\Builder;

class ContractVersionMetric extends BaseMetric
{
    protected $filters = [
        'contract' => ['year', 'company'],
        'version' => ['version'],
        'direction' => ['direction'],
    ];

    public static function getQuery(): Builder
    {
        return ContractVersion::query();
    }

    public static function getDateField(): string
    {
        return 'date';
    }

    public static function getQueryValue($query): int
    {
        $sum = 0;
        foreach ($query->get() as $contractVersion) {
            $prev = $contractVersion->prev;
            if ($prev === null) {
                // первая версия
                $sum += $contractVersion->sum;
            } else {
                // изменение
                $sum += ($contractVersion->sum - $prev->sum);
            }
        }

        return $sum;
    }

    protected function filterDirection(&$query, array $values)
    {
        if (count($values) === 0) {
            return;
        }

        $programs = collect();
        foreach ($values as $directionString) {
            $direction = Direction::from($directionString);
            $programs = $programs->concat(
                Direction::toPrograms($direction)
            );
        }
        $programs = $programs->unique();

        $query->whereHas('programs', fn($q) => $q->whereIn('program', $programs));
    }


    protected function filterContract(&$query, $value, $field)
    {
        $query->whereHas('contract', fn($q) => $q->where($field, $value));
    }

    protected function filterVersion(&$query, $value)
    {
        // 1 "только первая", 0 "только изменение"
        $sign = $value === 1 ? '=' : '>';

        $query->whereRaw("created_at $sign (
            select min(cv2.created_at) 
            from contract_versions as cv2
            where cv2.contract_id = contract_versions.contract_id
        )");
    }
}