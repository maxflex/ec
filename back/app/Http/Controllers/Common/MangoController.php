<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Utils\Mango;
use Illuminate\Http\Request;

/**
 * Mango realtime events
 */
class MangoController extends Controller
{
    public function __invoke($event, Request $request)
    {
        Mango::handle($event, $request->input('json'));
    }
}
