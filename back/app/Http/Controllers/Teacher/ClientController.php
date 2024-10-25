<?php

namespace App\Http\Controllers\Teacher;

use Illuminate\Http\Request;

class ClientController extends \App\Http\Controllers\Admin\ClientController
{

    public function index(Request $request)
    {
        $request->merge([
            'head_teacher_id' => auth()->id()
        ]);
        return parent::index($request);
    }
}