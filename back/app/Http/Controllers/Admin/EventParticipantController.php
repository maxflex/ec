<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventParticipantResource;
use App\Models\Client;
use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\Teacher;
use Illuminate\Http\Request;

class EventParticipantController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'event_id' => 'required',
        ]);

        $participants = EventParticipant::query()
            ->with('entity')
            ->where('event_id', $request->event_id)
            ->orderBy('entity_type')
            ->get();

        // Загрузить отношения в зависимости от типа
        $participants->loadMorph('entity', [
            Client::class => ['directions'], // только для клиента
            Teacher::class => [],             // ничего не грузим
        ]);

        return paginate(EventParticipantResource::collection($participants));
    }

    public function update(EventParticipant $eventParticipant, Request $request)
    {
        $eventParticipant->update($request->all());
    }

    /**
     * Сохранить участников из /event/[id]/participants
     */
    public function store(Request $request)
    {
        $request->validate([
            'event_id' => ['required', 'exists:events,id'],
            'selected' => ['required'],
        ]);
        $selected = collect($request->input('selected'));
        $event = Event::find($request->event_id);
        $existing = $event->participants->keyBy(
            fn (EventParticipant $p) => implode('-', [$p->entity_id, $p->entity_type])
        );

        foreach ($selected as $key) {
            // 1. Создаём только новых
            if ($existing->has($key)) {
                continue;
            }

            [$entityId, $entityType] = explode('-', $key);
            $event->participants()->create([
                'entity_id' => $entityId,
                'entity_type' => $entityType,
            ]);
        }

        // 2. Удаляем тех, кого больше нет в selected
        $existing
            ->filter(fn (EventParticipant $p, string $key) => ! $selected->contains($key))
            ->each
            ->delete();
    }

    public function destroy(EventParticipant $eventParticipant)
    {
        $eventParticipant->delete();
    }
}
