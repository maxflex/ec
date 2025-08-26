<?php

namespace App\Utils;

use App\Enums\Company;
use App\Models\ContractPayment;
use App\Models\OtherPayment;
use Illuminate\Support\Facades\DB;

/**
 * Платежи клиентов по договорам + остальные payments
 */
class AllPayments
{
    public static function query()
    {
        $otherPayments = OtherPayment::selectRaw(sprintf('
            first_name, last_name, middle_name,
            is_return, method, is_confirmed, `date`,
            "%s" as `company`, `purpose`, NULL as contract_id,
            `sum`, id, pko_number, NULL as client_id
        ',
            Company::ooo->value, // прочие платежи всегда ООО
        ));

        $contractPayments = ContractPayment::selectRaw('
            first_name, last_name, middle_name,
            is_return, method, is_confirmed, `date`,
            `company`, NULL as `purpose`, contract_id,
            `sum`, contract_payments.id, pko_number, `client_id`
        ')
            ->join('contracts as c', 'c.id', '=', 'contract_id')
            ->join('clients as cl', 'cl.id', '=', 'c.client_id');

        $cte = $otherPayments->union($contractPayments);

        return DB::table('payments')->withExpression('payments', $cte);
    }
}
