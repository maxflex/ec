<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventListResource;
use App\Http\Resources\EventResource;
use App\Models\Client;
use App\Models\Event;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;

class EventController extends Controller
{
    protected $filters = [
        'equals' => ['year'],
        'client' => ['client_id'],
        'teacher' => ['teacher_id'],
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'year' => ['required'],
        ]);

        $query = Event::withCount('participants')->latest();

        // конфиденциальные события видны только админам
        if (get_class(auth('crm')->user()) !== User::class) {
            $query->where('is_private', false);
        }

        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, EventListResource::class);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $event = auth()->user()->events()->create($request->all());
        $event->loadCount('participants');

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

    protected function filterClient(&$query, $clientId)
    {
        $where = [
            ['entity_type', Client::class],
            ['entity_id', $clientId],
        ];
        $query
            ->whereHas('participants', fn ($q) => $q->where($where))
            ->with('participants', fn ($q) => $q->where($where));
    }

    protected function filterTeacher(&$query, $teacherId)
    {
        $where = [
            ['entity_type', Teacher::class],
            ['entity_id', $teacherId],
        ];
        $query
            ->whereHas('participants', fn ($q) => $q->where($where))
            ->with('participants', fn ($q) => $q->where($where));
    }
}
