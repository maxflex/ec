<?php

namespace App\Utils;

use Illuminate\Support\Facades\DB;

class Swamp
{
    public static function query()
    {
        /**
         * Программы последней версии договора
         */
        $swampsCte = DB::table('contract_version_programs', 'cvp')
            ->join('contract_versions as cv', fn($join) => $join
                ->on('cv.id', '=', 'cvp.contract_version_id')
                ->where('cv.is_active', true)
            )
            ->join('contracts as c', 'c.id', '=', 'cv.contract_id')
            ->leftJoin(
                'client_groups as cg',
                'cg.contract_version_program_id',
                '=',
                'cvp.id'
            )
            ->selectRaw(<<<SQL
                cvp.id,
                (
                    select cast(sum(`lessons`) as unsigned)
                    from contract_version_program_prices 
                    where contract_version_program_id = cvp.id
                ) as `lessons`,
                (
                    select count(*) from `client_lessons` 
                    where contract_version_program_id = cvp.id
                ) as `lessons_passed`,
                `group_id`,
                cv.contract_id,
                c.year,
                cvp.program
            SQL
            );

        return DB::table('swamps')->withExpression('swamps', $swampsCte);
    }
}