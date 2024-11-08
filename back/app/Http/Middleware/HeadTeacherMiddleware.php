<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HeadTeacherMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->user()->entity->is_head_teacher) {
            abort(403);
        }
        return $next($request);
    }
}
