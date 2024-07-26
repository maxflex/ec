<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\MacroListResource;
use App\Models\Macro;
use Illuminate\Http\Request;

class MacroController extends Controller
{
    public function index(Request $request)
    {
        $query = Macro::orderBy('title');
        return $this->handleIndexRequest($request, $query, MacroListResource::class);
    }

    public function show(Macro $macro)
    {
        return $macro;
    }

    public function update(Macro $macro, Request $request)
    {
        $macro->update($request->all());
    }
}
