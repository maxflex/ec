<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Request as ClientRequest;
use App\Utils\AlfaPayment;
use Illuminate\Http\Request;

class MenuCountsController extends Controller
{
    public function __invoke(Request $request)
    {
        return [
            'requests' => ClientRequest::getMenuCount(),
            'alfa-payments' => count(AlfaPayment::getAllPayments()),
        ];
    }
}
