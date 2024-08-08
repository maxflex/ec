<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Utils\Stats\Stats;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    /**
     * @method POST
     */
    public function __invoke(Request $request)
    {
        return Stats::getData(
            $request->mode,
            $request->page,
            $request->metrics,
        );
    }
}
