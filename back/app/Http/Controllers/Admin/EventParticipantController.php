<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventWithParticipantsResource;
use App\Models\Client;
use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\Teacher;
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
     * Сохранить участников из people-selector
     */
    public function store(Request $request)
    {
        $request->validate([
            'id' => ['required', 'exists:events,id'],
            'selected' => ['required'],
        ]);
        $event = Event::find($request->id);
        $event->participants()->delete();
        foreach ($request->input('selected') as $key => $ids) {
            $class = $key === 'clients' ? Client::class : Teacher::class;
            foreach ($ids as $id) {
                $event->participants()->create([
                    'entity_type' => $class,
                    'entity_id' => $id,
                ]);
            }
        }
    }
}
