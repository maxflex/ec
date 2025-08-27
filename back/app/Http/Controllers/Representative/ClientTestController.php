<?php

namespace App\Http\Controllers\Representative;

use App\Events\ClientTestUpdatedEvent;
use App\Http\Controllers\Controller;
use App\Http\Resources\ClientTestResource;
use App\Models\ClientTest;
use Illuminate\Http\Request;

class ClientTestController extends Controller
{
    public function index(Request $request)
    {
        $query = ClientTest::query()
            ->where('client_id', auth()->user()->client_id)
            ->latest();

        return $this->handleIndexRequest($request, $query, ClientTestResource::class);
    }

    public function show($id)
    {
        $clientTest = ClientTest::query()
            ->whereId($id)
            ->where('client_id', auth()->user()->client_id)
            ->firstOrFail();

        abort_if($clientTest->is_timed_out, 404);

        return new ClientTestResource($clientTest);
    }

    public function start(ClientTest $clientTest)
    {
        $clientTest->start();
        ClientTestUpdatedEvent::dispatch($clientTest);
    }

    public function finish(Request $request)
    {
        $activeTest = ClientTest::getActive(auth()->user()->client_id);

        abort_if(! $activeTest, 417);

        $activeTest->finish($request->answers);

        ClientTestUpdatedEvent::dispatch($activeTest);
    }

    public function active()
    {
        $activeTest = ClientTest::getActive(auth()->user()->client_id);

        abort_if(! $activeTest, 417);

        return new ClientTestResource($activeTest);
    }
}
