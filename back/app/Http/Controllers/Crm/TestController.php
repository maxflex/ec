<?php

namespace App\Http\Controllers\Crm;

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
        $test = Test::make($request->all());
        if ($request->has('pdf')) {
            $fileName = uniqid() . ".pdf";
            $request->file('pdf')->storeAs('public/tests', $fileName);
            $test->file = $fileName;
        }
        $test->save();
    }

    public function show(Test $test)
    {
        return new TestResource($test);
    }
}
