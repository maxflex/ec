<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\MacroListResource;
use App\Http\Resources\MacroResource;
use App\Models\Macro;
use Illuminate\Http\Request;

class MacroController extends Controller
{
    public function index(Request $request)
    {
        // Для списка используем отдельный ListResource и лёгкую выборку.
        $query = Macro::query()
            ->select(['id', 'title'])
            ->orderBy('title');

        return $this->handleIndexRequest($request, $query, MacroListResource::class);
    }

    public function show(Macro $macro)
    {
        return new MacroResource($macro);
    }

    public function update(Macro $macro, Request $request)
    {
        $macro->update($request->all());
    }
}
