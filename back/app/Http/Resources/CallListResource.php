<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CallListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $phone = $this->phone;
        return extract_fields($this, [
            'type', 'number', 'created_at', 'finished_at', 'answered_at',
            'is_missed', 'is_missed_callback', 'has_recording'
        ], [
            'user' => new PersonResource($this->user),
            'phone' => $phone === null ? null : extract_fields($phone, [
                'entity_type', 'comment'
            ], [
                'person' => new PersonResource($phone->entity)
            ]),
        ]);
    }
}
