<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TeacherComplaintController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'text' => ['required', 'string', 'min: 3'],
        ]);

        return auth()->user()->complaints()->create([
            'text' => $request->text,
        ]);
    }
}
