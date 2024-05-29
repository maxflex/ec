<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::orderBy('id', 'desc');
        return $this->handleIndexRequest($request, $query);
    }

    public function show(Client $client)
    {
        return new ClientResource($client);
    }

    public function update(Client $client, Request $request)
    {
        $client->update($request->all());
        $client->syncRelation($request->all(), 'phones');
        $client->parent->update($request->parent);
        $client->parent->syncRelation($request->parent, 'phones');
        return new ClientResource($client);
    }
}
