<?php

namespace App\Utils;

use Illuminate\Support\Facades\DB;

class Swamp
{
    public static function query()
    {
        $t = DB::table('contract_version_program_prices')
            ->selectRaw(<<<SQL
                contract_version_program_id,
                CAST(SUM(`lessons`) AS UNSIGNED) AS total_lessons,
                CAST(SUM(`price` * `lessons`) AS UNSIGNED) AS total_price
            SQL
            )
            ->groupBy('contract_version_program_id');

        $tp = DB::table('client_lessons')
            ->selectRaw(<<<SQL
                contract_version_program_id,
                CAST(SUM(`price`) AS UNSIGNED) AS total_price_passed
            SQL
            )
            ->groupBy('contract_version_program_id');

        /**
         * Программы последней версии договора
         */
        $swampsCte = DB::table('contract_version_programs', 'cvp')
            ->join('contract_versions as cv', fn($join) => $join
                ->on('cv.id', '=', 'cvp.contract_version_id')
                ->where('cv.is_active', true)
            )
            ->join('contracts as c', 'c.id', '=', 'cv.contract_id')
            ->joinSub($t, 't', 't.contract_version_program_id', '=', 'cvp.id')
            ->joinSub($tp, 'tp', 'tp.contract_version_program_id', '=', 'cvp.id')
            ->leftJoin(
                'client_groups as cg',
                'cg.contract_version_program_id',
                '=',
                'cvp.id'
            )
            ->selectRaw(<<<SQL
                cvp.id,
                t.total_lessons,
                t.total_price,
                tp.total_price_passed,
                `group_id`,
                cv.contract_id,
                c.year,
                c.client_id,
                cvp.program
            SQL
            );

        return DB::table('swamps')->withExpression('swamps', $swampsCte);
    }

    public static function filterStatus($query, string $status)
    {
        switch ($status) {
            case 'toFulfil':
                $query->whereRaw("group_id IS NULL AND total_price_passed < total_price");
                break;

            case 'exceedNoGroup':
                $query->whereRaw("group_id IS NULL AND total_price_passed > total_price");
                break;

            case 'completeNoGroup':
                $query->whereRaw("group_id IS NULL AND total_price_passed = total_price");
                break;

            case 'inProcess':
                $query->whereRaw("group_id IS NOT NULL AND total_price_passed < total_price");
                break;

            case 'exceedInGroup':
                $query->whereRaw("group_id IS NOT NULL AND total_price_passed > total_price");
                break;

            case 'completeInGroup':
                $query->whereRaw("group_id IS NOT NULL AND total_price_passed = total_price");
                break;
        }
    }
}