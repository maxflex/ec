<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\{ClientTestResource, TestResource};
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TestController extends Controller
{
    public function index(Request $request)
    {
        $query = Test::whereClient(auth()->user()->entity_id);
        return $this->handleIndexRequest($request, $query, ClientTestResource::class);
    }

    public function show(Test $test)
    {
        return new ClientTestResource($test);
    }

    public function start(Test $test)
    {
        $test->start(auth()->user()->entity_id);
    }

    public function active()
    {
        $clientId = auth()->user()->entity_id;
        $test = Test::active($clientId)->first();
        if ($test === null) {
            return response(null, 404);
        }
        $startedAt = $test->results[$clientId]['started_at'];
        return [
            'test' => new ClientTestResource($test),
            // сколько в секундах осталось на выполнение теста
            'seconds' => max(
                0,
                $test->minutes * 60 - Carbon::parse($startedAt)->diffInSeconds(now())
            )
        ];
    }

    public function finish(Request $request)
    {
        $clientId = auth()->user()->entity_id;
        $test = Test::active($clientId)->first();
        if ($test === null) {
            return response(null, 404);
        }
        $test->finish($clientId, $request->answers);
    }

    public function result(Test $test)
    {
        $clientId = auth()->user()->entity_id;
        return $test->results[$clientId];
    }
}
