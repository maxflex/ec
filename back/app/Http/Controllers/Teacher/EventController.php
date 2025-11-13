<?php

namespace App\Http\Controllers\Teacher;

use Illuminate\Http\Request;

class EventController extends \App\Http\Controllers\Admin\EventController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->merge([
            'teacher_id' => auth()->id(),
        ]);

        return parent::index($request);
    }
}
