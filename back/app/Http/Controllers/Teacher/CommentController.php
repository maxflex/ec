<?php

namespace App\Http\Controllers\Teacher;

use App\Models\Report;
use Illuminate\Http\Request;

class CommentController extends \App\Http\Controllers\Admin\CommentController
{
    public function __construct(Request $request)
    {
        abort_if($request->entity_type !== Report::class, 403);
    }
}
