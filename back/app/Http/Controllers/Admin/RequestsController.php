<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\{RequestListResource, RequestResource};
use App\Models\Client;
use App\Models\Request as ClientRequest;
use Illuminate\Http\Request;

/**
 * Бред: если назвать RequestController, а не RequestSController,
 * то не работает XDebug. Чёртов бред, кучу времени потратил
 */
class RequestsController extends Controller
{
    protected $filters = [
        'equals' => [
            'status', 'direction', 'id'
        ]
    ];

    public function index(Request $request)
    {
        // временно: заявки клиента по ассоциативному механизму
        // было устно сказано добавить так
        if ($request->has('client_id')) {
            $client = Client::find($request->input('client_id'));
            return paginate(
                RequestListResource::collection($client->requests)
            );
        }

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
        $clientRequest->syncRelation($request->all(), 'phones');
        return new RequestListResource($clientRequest);
    }

    public function associated($id)
    {
        $clientRequest = ClientRequest::findOrFail($id);
        return RequestListResource::collection($clientRequest->getAssociatedRequests()->sortByDesc('created_at'));
    }

    public function destroy($id)
    {
        $clientRequest = ClientRequest::findOrFail($id);
        $clientRequest->phones->each->delete();
        $clientRequest->delete();
    }
}
