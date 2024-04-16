<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientTestResource;
use App\Models\ClientTest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TestController extends Controller
{
    public function index(Request $request)
    {
        $query = ClientTest::where('client_id', auth()->id());
        return $this->handleIndexRequest($request, $query, ClientTestResource::class);
    }

    public function results(ClientTest $clientTest)
    {
        return new ClientTestResource($clientTest);
    }

    public function start(ClientTest $clientTest)
    {
        $clientTest->started_at = now()->format('Y-m-d H:i:s');
        $clientTest->save();
    }

    public function active()
    {
        $activeTest = ClientTest::active()->where('client_id', auth()->id())->first();
        if ($activeTest === null) {
            return response(null, 404);
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
        $activeTest = ClientTest::active(auth()->id())->first();
        if ($activeTest === null) {
            return response(null, 404);
        }
        $activeTest->finished_at = now()->format('Y-m-d H:i:s');
        $activeTest->answers = $request->answers;
        $activeTest->save();
    }
}
