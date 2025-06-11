<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Events\RequestUpdatedEvent;
use App\Http\Resources\RequestListResource;
use App\Http\Resources\RequestResource;
use App\Models\Client;
use App\Models\Request as ClientRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Бред: если назвать RequestController, а не RequestSController,
 * то не работает XDebug. Чёртов бред, кучу времени потратил
 */
class RequestsController extends Controller
{
    protected $filters = [
        'equals' => ['status', 'id'],
        'findInSet' => ['direction'],
        'client' => ['client_id'],
    ];

    public function index(Request $request)
    {
        $query = ClientRequest::query()
            ->with('phones', 'responsibleUser', 'client')
            ->withCount('comments')
            ->latest();

        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, RequestListResource::class);
    }

    public function store(Request $request)
    {
        $clientRequest = auth()->user()->requests()->create(
            $request->all()
        );
        sync_relation($clientRequest, 'phones', $request->all());

        $clientRequest->refresh();
        RequestUpdatedEvent::dispatch($clientRequest);

        return new RequestListResource($clientRequest);
    }

    public function show($id)
    {
        $clientRequest = ClientRequest::findOrFail($id);

        return new RequestResource($clientRequest);
    }

    public function update($id, Request $request)
    {
        $clientRequest = ClientRequest::findOrFail($id);
        $clientRequest->update($request->all());
        sync_relation($clientRequest, 'phones', $request->all());

        RequestUpdatedEvent::dispatch($clientRequest);

        return new RequestListResource($clientRequest);
    }

    public function associated($id)
    {
        $clientRequest = ClientRequest::findOrFail($id);

        return RequestListResource::collection($clientRequest->getAssociatedRequests()->sortByDesc('created_at'));
    }

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $clientRequest = ClientRequest::findOrFail($id);
            RequestUpdatedEvent::dispatch($clientRequest);
            $clientRequest->phones->each->delete();
            $clientRequest->delete();
        });
    }

    // временно: заявки клиента по ассоциативному механизму
    // было устно сказано добавить так
    public function filterClient($query, $clientId)
    {
        $client = Client::find($clientId);
        $query->whereIn('id', collect($client->requests)->pluck('id'));
    }
}
