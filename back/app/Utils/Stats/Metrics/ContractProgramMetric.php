<?php

namespace App\Utils\Stats\Metrics;

use App\Models\ContractVersionProgram;
use Illuminate\Database\Eloquent\Builder;

class ContractProgramMetric extends BaseMetric
{
    protected $filters = [
        'findInSet' => ['program', 'year'],
    ];

    public function getDateField(): string
    {
        return '`date`';
    }

    public function getBaseQuery(): Builder
    {
        return ContractVersionProgram::query()
            ->select('contract_version_programs.*')
            ->join('contract_versions as cv', 'cv.id', '=', 'contract_version_programs.contract_version_id')
            ->join('contracts as c', 'cv.contract_id', '=', 'c.id');
    }

    /**
     * +1 когда программа появляется (ее не было в пред версии договора)
     */
    public function aggregate($query): int
    {
        $count = 0;
        foreach ($query->get() as $cvp) {
            $version = $cvp->contractVersion;
            // @var ContractVersion $prev
            $prev = $version->prev;
            if ($prev === null) {
                // первая версия
                $count++;
            } else {
                // изменение
                if (! $prev->programs()->where('program', $cvp->program)->exists()) {
                    $count++;
                }
            }
        }

        return $count;
    }
}
