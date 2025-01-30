<?php

namespace App\Http\Controllers\Teacher;

use Illuminate\Http\Request;

class ClientReviewController extends \App\Http\Controllers\Admin\ClientReviewController
{
    public function index(Request $request)
    {
        $request->merge([
            'teacher_id' => auth()->id(),
            'unique' => 'program',
            'type' => 1,
        ]);

        return parent::index($request);
    }
}
