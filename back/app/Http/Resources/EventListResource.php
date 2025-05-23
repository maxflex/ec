<?php

namespace App\Http\Resources;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Event
 */
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
            'date', 'name', 'time', 'time_end', 'is_afterclass',
            'description', 'is_private', 'telegram_lists_count',
        ], [
            'user' => new PersonResource($this->user),
            'participant' => $this->whenLoaded(
                'participants',
                fn () => extract_fields($this->participants[0], [
                    'confirmation',
                ])
            ),
            'participants' => $this->participants()
                ->selectRaw('confirmation, COUNT(*) as cnt')
                ->groupBy('confirmation')
                ->pluck('cnt', 'confirmation'),
        ]);
    }
}
