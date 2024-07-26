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

    public function show(Test $test)
    {
        return new TestResource($test);
    }

    public function uploadPdf(Request $request, Test $test)
    {
        if ($request->has('pdf')) {
            $file = uniqid() . ".pdf";
            $request->file('pdf')->storeAs('tests', $file);
            $test->file = $file;
            $test->save();
        }
    }
}
