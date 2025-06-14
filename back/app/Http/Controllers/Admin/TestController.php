<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TestResource;
use App\Models\Test;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index(Request $request)
    {
        $query = Test::latest();

        return $this->handleIndexRequest($request, $query, TestResource::class);
    }

    public function store(Request $request)
    {
        $test = auth()->user()->tests()->create($request->all());

        return new TestResource($test);
    }

    public function update(Request $request, Test $test)
    {
        $test->update($request->all());

        return new TestResource($test);
    }

    public function show(Test $test)
    {
        return new TestResource($test);
    }

    public function destroy(Test $test)
    {
        $test->delete();
    }
}
