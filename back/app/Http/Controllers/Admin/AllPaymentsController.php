<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllPaymentsResource;
use App\Utils\AllPayments;
use Illuminate\Http\Request;

/**
 * Платежи по договору + платежи клиентов
 */
class AllPaymentsController extends Controller
{
    protected $filters = [
        'equals' => ['company', 'is_confirmed'],
        'findInSet' => ['method'],
        'null' => ['contract_id'],
    ];

    public function __invoke(Request $request)
    {
        $query = AllPayments::query()
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc');

        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, AllPaymentsResource::class);
    }
}
