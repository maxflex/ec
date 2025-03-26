<?php

namespace App\Http\Resources;

use App\Models\Client;
use App\Models\Event;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Event */
class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $participants = [];
        foreach (['clients', 'teachers'] as $key) {
            $participants[$key] = $this->participants()
                ->with('entity')
                ->where('entity_type', $key === 'clients' ? Client::class : Teacher::class)
                ->get()
                ->sortBy([
                    ['entity.last_name', 'asc'],
                    ['entity.first_name', 'asc'],
                ])
                ->values()
                ->map(fn ($p) => extract_fields($p, [
                    'confirmation',
                ], [
                    'entity' => new PersonResource($p->entity),
                ]));
        }

        return extract_fields($this, ['*'], [
            'telegram_lists' => $this->telegramLists,
            'user' => new PersonResource($this->user),
            'participants' => $participants,
        ]);
    }
}
