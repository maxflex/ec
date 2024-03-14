<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\TestResource;
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
        $clientId = auth()->user()->entity_id;
        $results = $test->results;
        $results[$clientId] = [
            'started_at' => now()->format('Y-m-d H:i:s')
        ];
        $test->results = $results;
        $test->save();
        // $results = $test->results;
        //     if (in_array($test->id, $request->ids)) {
        //         $results[$client->id] = null;
        //     } else {
        //         unset($results[$client->id]);
        //     }
        //     $test->results = $results;
        //     $test->save();
    }


    public function active()
    {
        $clientId = auth()->user()->entity_id;
        $test = Test::getActive($clientId);
        return new TestResource($test);
        // $tests = Test::whereClient($clientId)->get();
        // foreach($tests as $test) {

        // }
    }
}
