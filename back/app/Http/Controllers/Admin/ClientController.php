<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClientRequest;
use App\Http\Resources\ClientListResource;
use App\Http\Resources\ClientResource;
use App\Http\Resources\PersonResource;
use App\Models\Client;
use App\Models\Phone;
use App\Models\Representative;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    protected $filters = [
        'equals' => ['status'],
        'contract' => ['year'],
        'search' => ['q'],
        'request' => ['request_id'],
        'headTeacher' => ['head_teacher_id'],
        'year' => ['year'],
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

    public function store(ClientRequest $request)
    {
        $client = auth()->user()->clients()->create($request->all());
        $representative = $client->representative()->create($request->representative);

        sync_relation($client, 'phones', $request->all());
        sync_relation($representative, 'phones', $request->representative);

        if ($request->has('request_id')) {
            \App\Models\Request::find($request->request_id)->update([
                'client_id' => $client->id,
            ]);
        }

        return new ClientListResource($client);
    }

    /**
     * Нужно для редиректа на страницу клиента по ID родителя
     */
    public function representative(Representative $representative)
    {
        return new PersonResource($representative->client);
    }

    public function update(Client $client, ClientRequest $request)
    {
        $client->update($request->all());
        $client->representative->update($request->representative);

        sync_relation($client, 'phones', $request->all());
        sync_relation($client->representative, 'phones', $request->representative);

        return new ClientResource($client);
    }

    public function destroy(Client $client)
    {
        DB::transaction(function () use ($client) {
            $client->representative->phones->each->delete();
            $client->representative->delete();
            $client->phones->each->delete();
            $client->delete();
        });
    }

    protected function filterSearch($query, $value)
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

    protected function filterContract($query, $value, $field)
    {
        $query->whereHas('contracts', fn ($q) => $q->where($field, $value));
    }

    protected function filterRequest($query, $requestId)
    {
        $numbers = Phone::where('entity_id', $requestId)
            ->where('entity_type', \App\Models\Request::class)
            ->pluck('number');
        $query->where(fn ($q) => $q
            ->whereHas('phones', fn ($q) => $q->whereIn('number', $numbers))
            ->orWhereHas('representative.phones', fn ($q) => $q->whereIn('number', $numbers))
        );
    }

    // https://doc.ege-centr.ru/tasks/834
    protected function filterHeadTeacher($query, $headTeacherId)
    {
        $query->where('head_teacher_id', $headTeacherId);
    }

    protected function filterYear($query, $year)
    {
        $query->whereHas('contracts', fn ($q) => $q->where('year', $year));
    }

    protected function getAvailableYears($query): Collection
    {
        return $query->join('contracts as c', 'c.client_id', '=', 'clients.id')->pluck('year');
    }
}
