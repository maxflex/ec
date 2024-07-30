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
                'contract_versions.id',
            )->selectRaw(<<<SQL
                c.id as `contract_id`,
                cvp.id as `contract_version_program_id`,
                c.year,
                cvp.program,
                cvp.is_closed
            SQL);

        $groupsCte = DB::table('contract_group as cg')
            ->join('groups as g', 'g.id', '=', 'cg.group_id')
            ->selectRaw(<<<SQL
                g.id as `group_id`,
                g.year,
                g.program,
                cg.contract_id
            SQL);

        $swampsCte = DB::table('c')
            ->withExpression('c', $contractVersionProgramsCte)
            ->withExpression('g', $groupsCte)
            ->join(
                'g',
                fn ($join) => $join
                    ->on('g.contract_id', '=', 'c.contract_id')
                    ->on('g.program', '=', 'c.program')
                    ->on('g.year', '=', 'c.year'),
                'full outer'
            )
            ->selectRaw(<<<SQL
                UUID() as `id`,
                g.group_id,
                c.is_closed,
                `contract_version_program_id`,
                ifnull(c.contract_id, g.contract_id) as `contract_id`,
                ifnull(c.program, g.program) as `program`,
                ifnull(c.year, g.year) as `year`
            SQL);

        return DB::table('swamps')->withExpression('swamps', $swampsCte);
    }
}

/**
 * FULL OUTER JOIN в Mysql не работает
 *
 $lastVersionsCte =  ContractVersion::selectRaw(<<<SQL
            contract_id,
            MAX(version) as last_version
        SQL)->groupBy('contract_id');

        $contractVersionProgramsCte = DB::table('contract_version_programs', 'cvp')
            ->withExpression('lv', $lastVersionsCte)
            ->join('contract_versions as cv', 'cv.id', '=', 'cvp.contract_version_id')
            ->join(
                'lv',
                fn ($join) => $join
                    ->on('cv.contract_id', '=', 'lv.contract_id')
                    ->on('cv.version', '=', 'lv.last_version')
            )
            ->join('contracts as c', 'c.id', '=', 'cv.contract_id')
            ->selectRaw(<<<SQL
                cv.contract_id,
                c.year,
                cvp.id as `contract_version_program_id`,
                cvp.program,
                cvp.is_closed
            SQL);

        $groupsCte = DB::table('contract_group', 'cg')
            ->join('groups as g', 'g.id', '=', 'cg.group_id')
            ->selectRaw(<<<SQL
                g.year,
                g.program,
                cg.group_id,
                cg.contract_id
            SQL);

        $swampsCte = DB::table('c')
            ->withExpression('c', $contractVersionProgramsCte)
            ->withExpression('g', $groupsCte)
            ->join(
                'g',
                fn ($join) => $join
                    ->on('g.contract_id', '=', 'c.contract_id')
                    ->on('g.program', '=', 'c.program')
                    ->on('g.year', '=', 'c.year'),
                type: 'full outer'
            )
            ->selectRaw(<<<SQL
                UUID() as `id`,
                g.group_id,
                c.is_closed,
                `contract_version_program_id`,
                ifnull(c.contract_id, g.contract_id) as `contract_id`,
                ifnull(c.program, g.program) as `program`,
                ifnull(c.year, g.year) as `year`
            SQL);
 */
