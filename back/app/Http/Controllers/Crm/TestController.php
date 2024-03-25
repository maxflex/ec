<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Http\Resources\TestResource;
use App\Models\Client;
use App\Models\Test;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index(Request $request)
    {
        $query = Test::latest();
        return $this->handleIndexRequest($request, $query);
    }

    public function store(Request $request)
    {
        $test = Test::create($request->all());
        return new TestResource($test);
    }

    public function update(Request $request, Test $test)
    {
        $test->update($request->all());
        return new TestResource($test);
    }

    public function uploadPdf(Request $request, Test $test)
    {
        if ($request->has('pdf')) {
            $file = uniqid() . ".pdf";
            $request->file('pdf')->storeAs('public/tests', $file);
            $test->file = $file;
            $test->save();
        }
    }

    public function addClient(Client $client, Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:tests,id']
        ]);

        Test::whereIn('id', $request->ids)->get()->each(function ($test) use ($client) {
            $test->addClient($client);
        });
    }

    public function show(Test $test)
    {
        return new TestResource($test);
    }
}
