<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return extract_fields($this, ['*'], [
            // 'participants' => PersonListResource::collection($this->participants),
            'user' => new PersonResource($this->user),
            'participants' => PersonListResource::collection(
                $this->participants->map(fn ($p) => $p->entity)
            )
        ]);
    }
}
