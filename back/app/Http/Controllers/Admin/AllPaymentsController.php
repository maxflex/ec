<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllPaymentResource;
use App\Utils\AllPayments;
use Illuminate\Http\Request;

/**
 * Платежи по договору + платежи клиентов
 */
class AllPaymentsController extends Controller
{
    protected $filters = [
        'equals' => [
            'year', 'method', 'company', 'is_confirmed'
        ]
    ];

    public function __invoke(Request $request)
    {
        $query = AllPayments::query();
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, AllPaymentResource::class);
    }
}
