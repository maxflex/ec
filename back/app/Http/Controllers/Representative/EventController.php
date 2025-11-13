<?php

namespace App\Http\Controllers\Representative;

use Illuminate\Http\Request;

class EventController extends \App\Http\Controllers\Admin\EventController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->merge([
            'client_id' => auth()->user()->client_id,
        ]);

        return parent::index($request);
    }
}
