<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PassResource;
use App\Models\Pass;
use Illuminate\Http\Request;

class PassController extends Controller
{
    public function index()
    {

    }

    public function store(Request $request)
    {
        $pass = auth()->user()->entity->passes()->create(
            $request->all()
        );
        return new PassResource($pass);
    }

    public function destroy(Pass $pass)
    {
        $pass->delete();
    }
}
