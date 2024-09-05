<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientTestResource;
use App\Models\ClientTest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ClientTestController extends Controller
{
    public function index(Request $request)
    {
        $query = ClientTest::query()
            ->where('client_id', auth()->id())
            ->latest();
        return $this->handleIndexRequest($request, $query, ClientTestResource::class);
    }

    public function show($id)
    {
        $clientTest = ClientTest::query()
            ->whereId($id)
            ->where('client_id', auth()->id())
            ->firstOrFail();
        return new ClientTestResource($clientTest);
    }

    public function start(ClientTest $clientTest)
    {
        $clientTest->start();
    }

    public function active()
    {
        $activeTest = ClientTest::query()
            ->active()
            ->where('client_id', auth()->id())
            ->first();

        if ($activeTest === null) {
            return response(null);
        }

        return [
            'test' => new ClientTestResource($activeTest),
            // сколько в секундах осталось на выполнение теста
            'seconds' => max(
                0,
                $activeTest->minutes * 60 - Carbon::parse($activeTest->started_at)->diffInSeconds(now())
            )
        ];
    }

    public function finish(Request $request)
    {
        $activeTest = ClientTest::query()
            ->where('client_id', auth()->id())
            ->active()
            ->first();
        if ($activeTest === null) {
            return response(null);
        }
        $activeTest->finish($request->answers);
    }
}
