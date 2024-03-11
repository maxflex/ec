<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
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
        Test::create($request->all());
    }
}
