<?php

namespace App\Http\Resources;

use App\Models\Error;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Error */
class ErrorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $extra = match ($this->code) {
            // Phone::class
            1000 => [
                'entity_id' => $this->entity->entity_id,
                'entity_type' => $this->entity->entity_type,
                'number' => $this->entity->number,
                'person' => new PersonResource($this->entity->entity),
            ],
            default => [
                'entity_id' => $this->entity_id,
                'entity_type' => $this->entity_type,
            ]
        };

        return extract_fields($this, ['code'], $extra);
    }
}
