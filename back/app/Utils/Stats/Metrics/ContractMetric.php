<?php

namespace App\Utils\Stats\Metrics;

use App\Enums\Direction;
use App\Models\ContractVersion;
use Illuminate\Database\Eloquent\Builder;

class ContractMetric extends BaseMetric
{
    protected $filters = [
        'contract' => ['year', 'company'],
        'version' => ['version'],
        'direction' => ['direction'],
    ];

    public function getDateField(): string
    {
        return '`date`';
    }

    public function getBaseQuery(): Builder
    {
        return ContractVersion::query()
            ->select('contract_versions.*')
            ->join('contracts as c', 'contract_versions.contract_id', '=', 'c.id');
    }

    public function aggregate($query): int
    {
        if (@$this->filterValues['aggregate'] === 'count') {
            return $query->count();
        }

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

    protected function filterDirection($query, array $values)
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

    protected function filterContract($query, $value, $field)
    {
        if ($field === 'year') {
            if (count($value)) {
                $query->whereIn('c.year', $value);
            }
        } else {
            $query->whereRaw("c.`$field` = ?", [$value]);
        }
    }

    protected function filterVersion($query, $value)
    {
        if ($value === 'firstInClient') {
            $firstInClient = ContractVersion::query()
                ->join('contracts', 'contract_versions.contract_id', '=', 'contracts.id')
                ->selectRaw('client_id, MIN(contract_versions.id) as `id`')
                ->groupBy('client_id');

            $query->leftJoinSub(
                $firstInClient,
                'first_in_client',
                'first_in_client.id',
                '=',
                'contract_versions.id'
            );
        } else {
            $firstInContract = ContractVersion::query()
                ->selectRaw('contract_id, MIN(id) as `id`')
                ->groupBy('contract_id');

            $query->leftJoinSub(
                $firstInContract,
                'first_in_contract',
                'first_in_contract.id',
                '=',
                'contract_versions.id'
            );
        }

        switch ($value) {
            case 'firstInClient':
                $query->whereNotNull('first_in_client.id');
                break;

            case 'firstInContract':
                $query->whereNotNull('first_in_contract.id');
                break;

            case 'recurringFirstInClient':
                $query->whereRaw('(
                    first_in_contract.id IS NOT NULL
                    AND EXISTS (
                        SELECT 1 FROM contracts c2
                        WHERE c2.client_id = c.client_id
                        AND c2.year < c.year
                    )
                )');
                break;

            case 'onlyChanges':
                $query->whereNull('first_in_contract.id');
                break;
        }
    }
}
