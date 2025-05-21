<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'date', 'name', 'time', 'time_end',
            'is_afterclass', 'participants_count',
            'description', 'is_private',
        ], [
            'telegram_lists_count' => $this->telegram_lists_count,
            'user' => new PersonResource($this->user),
            'participant' => $this->whenLoaded(
                'participants',
                fn () => extract_fields($this->participants[0], [
                    'confirmation',
                ])
            ),
        ]);
    }
}
