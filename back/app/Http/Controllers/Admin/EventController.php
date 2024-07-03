<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventListResource;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'year' => ['required']
        ]);
        $query = Event::query()
            ->whereYear($request->year)
            ->withCount('participants');
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, EventListResource::class);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $event = auth()->user()->entity->events()->create($request->all());
        return new EventListResource($event);
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        $event->loadCount('participants');
        return new EventResource($event);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        $event->update($request->all());
        $event->participants()->delete();
        foreach ($request->participants as $p) {
            $event->participants()->create([
                'entity_id' => $p['id'],
                'entity_type' => $p['entity_type']
            ]);
        }
        $event->loadCount('participants');
        return new EventListResource($event);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->participants->each->delete();
        $event->delete();
    }
}
