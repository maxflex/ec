<?php

namespace App\Http\Resources;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Event */
class EventWithParticipantsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return extract_fields($this, [], [
            'participants' => $this->participants()
                ->with('entity')
                ->get()
                ->sortBy([
                    ['entity.last_name', 'asc'],
                    ['entity.first_name', 'asc'],
                ])
                ->values()
                ->map(fn ($p) => extract_fields($p, [
                    'entity_id', 'entity_type', 'is_confirmed',
                    'is_visited',
                ], [
                    'entity' => new PersonWithPhotoResource($p->entity),
                ])),
        ]);
    }
}
