<?php

namespace App\Http\Controllers\Client;

use App\Http\Resources\GradeListForClientsResource;
use App\Models\Grade;
use Illuminate\Http\Request;

class GradeController extends \App\Http\Controllers\Admin\GradeController
{

    public function index(Request $request)
    {
        $request->merge([
            'client_id' => auth()->id()
        ]);

        return parent::index($request);
    }

    public function journal(Request $request)
    {
        return GradeListForClientsResource::collection(
            Grade::where('client_id', auth()->id())
                ->where('year', $request->year)
                ->where('quarter', $request->quarter)
                ->get()
        );
    }
}
