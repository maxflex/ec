<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\{TestResource, TestResultResource};
use App\Models\Test;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index(Request $request)
    {
        $query = Test::whereClient(auth()->user()->entity_id);
        return $this->handleIndexRequest($request, $query);
    }

    public function show(Test $test)
    {
        return new TestResource($test);
    }

    public function start(Test $test)
    {
        $test->start(auth()->user()->entity_id);
    }

    public function active()
    {
        return new TestResource(
            Test::getActive(auth()->user()->entity_id)
        );
    }

    public function finish(Request $request)
    {
        $clientId = auth()->user()->entity_id;
        Test::getActive($clientId)->finish($clientId, $request->answers);
    }

    public function result(Test $test)
    {
        $clientId = auth()->user()->entity_id;
        return $test->results[$clientId];
    }
}
