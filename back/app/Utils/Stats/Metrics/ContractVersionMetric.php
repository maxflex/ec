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

    public function getDateField(): string
    {
        return 'date';
    }

    public function getQuery(): Builder
    {
        return ContractVersion::query();
    }

    public function getQueryValue($query): int
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
            $programs = $programs->concat(
                Direction::from($directionString)->toPrograms()
            );
        }
        $programs = $programs->unique();

        $query->whereHas('programs', fn ($q) => $q->whereIn('program', $programs));
    }

    protected function filterContract(&$query, $value, $field)
    {
        if ($field === 'year') {
            if (count($value)) {
                $query->whereHas('contract', fn ($q) => $q->whereIn('year', $value));
            }
        } else {
            $query->whereHas('contract', fn ($q) => $q->where($field, $value));
        }

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
