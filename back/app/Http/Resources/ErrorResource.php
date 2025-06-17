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
                'number' => $this->entity->number,
                'person' => new PersonResource($this->entity->entity),
            ],
            2000 => [
                'person' => new PersonResource($this->entity->client),
            ],
        };

        return extract_fields($this, [
            'code', 'entity_id', 'entity_type',
        ], $extra);
    }
}
