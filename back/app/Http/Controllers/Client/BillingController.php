<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\BillingResource;
use App\Models\Contract;

class BillingController extends Controller
{
    public function __invoke()
    {
        $contracts = Contract::query()
            ->where('client_id', auth()->id())
            ->with('client.parent')
            ->with(['payments' => fn ($q) => $q->where('is_confirmed', true)->orderBy('date')])
            ->with(
                'versions',
                fn ($q) => $q
                    ->with(['payments' => fn ($q) => $q->orderBy('date')])
                    ->active()
            )
            ->orderBy('id', 'desc')
            ->get();

        return paginate(BillingResource::collection($contracts));
    }
}
