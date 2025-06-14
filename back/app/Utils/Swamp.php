<?php

namespace App\Utils;

use Illuminate\Support\Facades\DB;

/**
 * @DEPRICATED больше не используется
 */
class Swamp
{
    public static function query(?int $clientId = null)
    {
        // сумма занятий и цен из ContractVersionProgramPrice
        $totals = DB::table('contract_version_program_prices')
            ->selectRaw('
                contract_version_program_id,
                CAST(SUM(`lessons`) AS UNSIGNED) AS total_lessons,
                CAST(SUM(`price` * `lessons`) AS UNSIGNED) AS total_price
            ')
            ->groupBy('contract_version_program_id');

        // сумма цен за проведённые занятия из ClientLesson
        $totalPassed = DB::table('client_lessons')
            ->selectRaw('
                contract_version_program_id,
                CAST(SUM(`price`) AS UNSIGNED) AS total_price_passed
            ')
            ->groupBy('contract_version_program_id');

        /**
         * Программы последней версии договора
         */
        $swampsCte = DB::table('contract_version_programs', 'cvp')
            ->join('contract_versions as cv', fn ($join) => $join
                ->on('cv.id', '=', 'cvp.contract_version_id')
                ->where('cv.is_active', true)
            )
            ->join('contracts as c', 'c.id', '=', 'cv.contract_id')
            ->joinSub($totals, 't', 't.contract_version_program_id', '=', 'cvp.id')
            ->leftJoinSub($totalPassed, 'tp', 'tp.contract_version_program_id', '=', 'cvp.id')
            ->leftJoin(
                'client_groups as cg',
                'cg.contract_version_program_id',
                '=',
                'cvp.id'
            )
            ->when($clientId, fn ($q) => $q->where('c.client_id', $clientId))
            ->selectRaw('
                cvp.id,
                t.total_lessons,
                t.total_price,
                CAST(IFNULL(tp.total_price_passed, 0) AS UNSIGNED) AS `total_price_passed`,
                `group_id`,
                cv.contract_id,
                c.year,
                c.client_id,
                cvp.program
            ');

        return DB::table('swamps')->withExpression('swamps', $swampsCte);
    }

    public static function filterStatus($query, string $status)
    {
        switch ($status) {
            case 'toFulfil':
                $query->whereRaw('group_id IS NULL AND total_price_passed < total_price');
                break;

            case 'exceedNoGroup':
                $query->whereRaw('group_id IS NULL AND total_price_passed > total_price');
                break;

            case 'completeNoGroup':
                $query->whereRaw('group_id IS NULL AND total_price_passed = total_price');
                break;

            case 'inProcess':
                $query->whereRaw('group_id IS NOT NULL AND total_price_passed < total_price');
                break;

            case 'exceedInGroup':
                $query->whereRaw('group_id IS NOT NULL AND total_price_passed > total_price');
                break;

            case 'completeInGroup':
                $query->whereRaw('group_id IS NOT NULL AND total_price_passed = total_price');
                break;

            case 'noGroup':
                $query->whereRaw('group_id IS NULL');
                break;
        }
    }
}
