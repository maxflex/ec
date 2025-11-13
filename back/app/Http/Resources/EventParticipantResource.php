<?php

namespace App\Http\Resources;

use App\Models\EventParticipant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin EventParticipant */
class EventParticipantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'confirmation', 'is_me',
        ], [
            'entity' => new PersonResource($this->entity),
        ]);
    }
}
