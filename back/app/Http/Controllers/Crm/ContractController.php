<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContractResource;
use App\Models\Contract;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function index(Request $request)
    {
        $query = Contract::query()
            ->with('client')
            ->orderBy('id', 'desc');
        return $this->handleIndexRequest($request, $query, ContractResource::class);
    }
}
