<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientListResource;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    protected $filters = [
        // 'equals' => ['status'],
        'contract' => ['year'],
        'search' => ['q']
    ];

    public function index(Request $request)
    {
        $query = Client::orderBy('id', 'desc');
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, ClientListResource::class);
    }

    public function show(Client $client)
    {
        return new ClientResource($client);
    }

    public function store(Request $request)
    {
        $client = auth()->user()->entity->clients()->create($request->all());
        $client->syncRelation($request->all(), 'phones');
        $parent = $client->parent()->create($request->parent);
        $parent->syncRelation($request->parent, 'phones');
        if ($request->has('request_id')) {
            \App\Models\Request::find($request->request_id)->update([
                'client_id' => $client->id,
            ]);
        }
        return new ClientListResource($client);
    }

    public function update(Client $client, Request $request)
    {
        $client->update($request->all());
        $client->syncRelation($request->all(), 'phones');
        $client->parent->update($request->parent);
        $client->parent->syncRelation($request->parent, 'phones');
        return new ClientResource($client);
    }

    protected function filterSearch(&$query, $value)
    {
        $words = explode(' ', $value);
        $query->where(function ($q) use ($words) {
            foreach ($words as $word) {
                $q->where('first_name', 'like', "%{$word}%")
                    ->orWhere('last_name', 'like', "%{$word}%")
                    ->orWhere('middle_name', 'like', "%{$word}%");
            }
        });
    }

    protected function filterContract(&$query, $value, $field)
    {
        $query->whereHas('contracts', fn ($q) => $q->where($field, $value));
    }
}
