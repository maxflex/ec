<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\{RequestResource, RequestListResource};
use App\Models\Request as ClientRequest;
use Illuminate\Http\Request;

/**
 * Бред: если назвать RequestController, а не RequestSController,
 * то не работает XDebug. Чёртов бред, кучу времени потратил
 */
class RequestsController extends Controller
{
    protected $filters = [
        'equals' => ['status', 'program']
    ];

    public function index(Request $request)
    {
        $query = ClientRequest::query()
            ->with('phones', 'responsibleUser', 'client')
            ->latest();
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, RequestListResource::class);
    }

    public function store(Request $request)
    {
        $clientRequest = auth()->user()->entity->requests()->create(
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
}
