<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventParticipant;
use Illuminate\Http\Request;

class EventParticipantController extends Controller
{
    public function update(EventParticipant $eventParticipant, Request $request)
    {
        $eventParticipant->update($request->all());
    }
}
