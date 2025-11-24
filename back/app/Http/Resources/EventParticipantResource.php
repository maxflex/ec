<?php

namespace App\Http\Resources;

use App\Models\Client;
use App\Models\EventParticipant;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin EventParticipant */
class EventParticipantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Client|Teacher $entity */
        $entity = $this->entity;

        return extract_fields($this, [
            'confirmation', 'is_visited', 'is_me',
        ], [
            'entity' => new PersonResource($entity),
            'directions' => $this->when(
                auth()->user() instanceof User,
                fn () => $entity->directions,
            ),
        ]);
    }
}
