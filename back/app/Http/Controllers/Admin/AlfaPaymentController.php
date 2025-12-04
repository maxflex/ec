<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlfaPaymentResource;
use App\Utils\AlfaPayment;

class AlfaPaymentController extends Controller
{
    public function index()
    {
        $alfaPayments = AlfaPayment::getAllPayments();

        return paginate(
            AlfaPaymentResource::collection($alfaPayments)
        );
    }
}
