<?php

namespace App\Http\Controllers\Representative;

use App\Enums\EventParticipantConfirmation;
use App\Http\Resources\EventListResource;
use App\Models\Client;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EventController extends \App\Http\Controllers\Admin\EventController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->merge([
            'client_id' => auth()->user()->client_id,
        ]);

        return parent::index($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        $event->loadCount('participants');
        $event->load([
            'participants' => fn ($q) => $q
                ->where('entity_type', Client::class)
                ->where('entity_id', auth()->user()->client_id),
        ]);

        return new EventListResource($event);
    }

    /**
     * Подтверждение участия
     */
    public function update(Event $event, Request $request)
    {
        $request->validate([
            'confirmation' => Rule::enum(EventParticipantConfirmation::class),
        ]);

        $participant = $event->participants()
            ->where('entity_type', Client::class)
            ->where('entity_id', auth()->user()->client_id)
            ->firstOrFail();

        $participant->update([
            'confirmation' => $request->confirmation,
        ]);
    }
}
