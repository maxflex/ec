<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupVisitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $contractLessons = $this->contractLessons()->with('contract.client')->get();

        return extract_fields($this, [
            'dateTime'
        ], [
            'contractLessons' => $contractLessons->map(fn ($c) => extract_fields($c, [
                'status', 'is_remote', 'minutes_late'
            ], [
                'client' => new PersonResource($c->contract->client),
            ]))
        ]);
    }
}
