<?php

namespace App\Utils;

use App\Models\ClientPayment;
use App\Models\ContractPayment;
use Illuminate\Support\Facades\DB;

/**
 * Платежи клиентов по договорам + остальные client_payments
 */
class AllPayments
{
    public static function query()
    {
        $clientPayments = ClientPayment::selectRaw('
            client_id, is_return, method, is_confirmed, `date`,
            `company`, `year`, `purpose`, NULL as contract_id,
            `sum`, id, pko_number
        ');

        $contractPayments = ContractPayment::selectRaw('
            client_id, is_return, method, is_confirmed, `date`,
            `company`, `year`, NULL as purpose, contract_id,
            `sum`, contract_payments.id, pko_number
        ')->join('contracts as c', 'c.id', '=', 'contract_id');

        $cte = $clientPayments->union($contractPayments);

        return DB::table('payments')->withExpression('payments', $cte);
    }
}
