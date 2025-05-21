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
            'company', 'is_confirmed',
        ],
        'findInSet' => [
            'year', 'method',
        ],
    ];

    public function __invoke(Request $request)
    {
        $query = AllPayments::query()
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc');

        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, AllPaymentResource::class);
    }
}
