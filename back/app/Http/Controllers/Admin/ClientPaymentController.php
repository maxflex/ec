<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientPaymentResource;
use App\Models\ClientPayment;
use Illuminate\Http\Request;

class ClientPaymentController extends Controller
{
    protected $filters = [
        'equals' => ['year', 'method', 'client_id']
    ];

    public function index(Request $request)
    {
        $query = ClientPayment::query()
            ->with(['user', 'client'])
            ->latest();
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, ClientPaymentResource::class);
    }

    public function store(Request $request)
    {
        $clientPayment = auth()->user()->entity->clientPayments()->create($request->all());
        return new ClientPaymentResource($clientPayment);
    }

    public function show(ClientPayment $clientPayment)
    {
        return new ClientPaymentResource($clientPayment);
    }

    public function update(ClientPayment $clientPayment, Request $request)
    {
        $clientPayment->update($request->all());
        return new ClientPaymentResource($clientPayment);
    }

    public function destroy(ClientPayment $clientPayment)
    {
        $clientPayment->delete();
    }
}
