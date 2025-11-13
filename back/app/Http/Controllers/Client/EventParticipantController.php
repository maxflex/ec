<?php

namespace App\Http\Controllers\Client;

use App\Enums\EventParticipantConfirmation;
use App\Http\Controllers\Controller;
use App\Http\Resources\EventParticipantResource;
use App\Models\Client;
use App\Models\EventParticipant;
use App\Models\Representative;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EventParticipantController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'event_id' => 'required',
        ]);

        $participants = EventParticipant::query()
            ->with('entity')
            // ->where('confirmation', EventParticipantConfirmation::confirmed)
            ->where('event_id', $request->event_id)
            ->orderBy('entity_type')
            ->get();

        $user = $this->getMeUser();
        $participants->transform(function (EventParticipant $p) use ($user) {
            $p->is_me = $p->getIsMe($user);

            return $p;
        });

        // текущий клиент должен быть среди участников
        abort_unless($participants->some(fn (EventParticipant $p) => $p->is_me), 422);

        return paginate(EventParticipantResource::collection($participants));
    }

    /**
     * Чтобы representative -> в client
     * Нужно для определения is_me
     */
    private function getMeUser(): Client|Teacher
    {
        /** @var Client|Teacher|Representative $user */
        $user = auth()->user();

        if ($user instanceof Representative) {
            return $user->client;
        }

        return $user;
    }

    /**
     * Подтверждение участия
     */
    public function update(EventParticipant $eventParticipant, Request $request)
    {
        $request->validate([
            'confirmation' => Rule::enum(EventParticipantConfirmation::class),
        ]);

        $eventParticipant->update($request->all());

        return new EventParticipantResource($eventParticipant);
    }
}
