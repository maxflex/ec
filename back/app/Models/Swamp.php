<?php

namespace App\Models;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class Swamp
{
    const DISABLE_LOGS = true;

    public static function query(): Builder
    {
        $contractVersionProgramsCte = ContractVersion::query()
            ->lastVersions()
            ->join(
                'contracts as c',
                'c.id',
                '=',
                'contract_versions.contract_id'
            )
            ->join(
                'contract_version_programs as cvp',
                'cvp.contract_version_id',
                '=',
                'contract_versions.id'
            );

        $groupsCte = DB::table('contract_group as cg')
            ->join('groups as g', 'g.id', '=', 'cg.group_id');

        return DB::table('cvp')
            ->withExpression('cvp', $contractVersionProgramsCte)
            ->withExpression('g', $groupsCte)
            ->join(
                'g',
                'g.contract_id',
                '=',
                'cvp.contract_id',
                'left outer'
            )->selectRaw(<<<SQL
                cg.group_id,
                ifnull(c.id, cg.contract_id) as `contract_id`
            SQL);
    }
}
