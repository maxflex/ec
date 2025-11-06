<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PeopleSelectorResource;
use App\Http\Resources\PersonResource;
use App\Models\Client;
use App\Models\Event;
use App\Models\Teacher;
use Illuminate\Http\Request;

class PeopleSelectorController extends Controller
{
    public function __invoke(Request $request)
    {
        if ($request->has('event_id')) {
            $event = Event::findOrFail($request->event_id);
            [$clients, $teachers] = $this->getForEvent($event);
        } else {
            $clients = $this->getClients();
            $teachers = $this->getTeachers();
        }

        return [
            'clients' => PeopleSelectorResource::collection($clients),
            'teachers' => PersonResource::collection($teachers),
        ];
    }

    private function getForEvent(Event $event): array
    {
        $participants = $event->participants()->with('entity')->get();

        $result = [];
        foreach ([Client::class, Teacher::class] as $entityType) {
            $result[] = $participants
                ->where('entity_type', $entityType)
                ->map(fn ($e) => $e->entity)
                ->sortBy(['last_name', 'first_name', 'middle_name'])
                ->values();
        }

        return $result;
    }

    private function getClients()
    {
        return Client::canLogin()
            ->with(['contracts.versions.programs'])
            ->whereHas('contracts', fn ($q) => $q->where('year', current_academic_year()))
            ->orderByRaw('last_name, first_name, middle_name')
            ->get();
    }

    private function getTeachers()
    {
        return Teacher::canLogin()
            ->orderByRaw('last_name, first_name, middle_name')
            ->get();
    }
}
