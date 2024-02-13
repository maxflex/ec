<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function show(Client $client)
    {
        return new ClientResource($client);
    }
}
