<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function client(Client $client, Request $request)
    {
        $request->validate(['year' => ['required']]);
        return $client->getSchedule($request->year);
    }
}
