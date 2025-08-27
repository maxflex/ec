<?php

namespace App\Http\Controllers\Representative;

use App\Http\Resources\GradeResource;
use App\Models\Grade;
use Illuminate\Http\Request;

class GradeController extends \App\Http\Controllers\Admin\GradeController
{
    public function index(Request $request)
    {
        $request->merge([
            'client_id' => auth()->user()->client_id,
        ]);

        return parent::index($request);
    }

    public function journal(Request $request)
    {
        return GradeResource::collection(
            Grade::where('client_id', auth()->user()->client_id)
                ->where('year', $request->year)
                ->where('quarter', $request->quarter)
                ->get()
        );
    }
}
