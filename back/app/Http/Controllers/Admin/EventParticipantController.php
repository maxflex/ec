<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventWithParticipantsResource;
use App\Models\Event;
use App\Models\EventParticipant;
use Illuminate\Http\Request;

class EventParticipantController extends Controller
{
    public function update(EventParticipant $eventParticipant, Request $request)
    {
        $eventParticipant->update($request->all());
    }

    /**
     * Для редактирования участников события
     */
    public function show(Event $event)
    {
        return new EventWithParticipantsResource($event);
    }

    /**
     * Сохранить участников в EventParticipantsDialog
     */
    public function store(Request $request)
    {
        $request->validate([
            'id' => ['required', 'exists:events,id'],
            'participants' => ['required', 'array'],
        ]);
        $event = Event::find($request->id);
        $event->syncRelation($request->all(), 'participants');
    }
}
