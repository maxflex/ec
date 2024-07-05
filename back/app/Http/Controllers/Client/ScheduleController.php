<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\LessonListResource;
use App\Models\Client;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function client(Client $client, Request $request)
    {
        $request->validate([
            'year' => ['required'],
        ]);
        if ($client->id !== auth()->id()) {
            return response(status: 412);
        }
        return LessonListResource::collection(
            $client->getSchedule($request->year)
        );
    }
}
