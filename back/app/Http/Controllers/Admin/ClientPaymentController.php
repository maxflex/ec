<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientPayment;
use Illuminate\Http\Request;

class ClientPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = ClientPayment::query()
            ->latest();
        return $this->handleIndexRequest($request, $query);
    }
}
