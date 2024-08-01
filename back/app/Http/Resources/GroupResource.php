<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return extract_fields($this, ['*'], [
            'teeth' => $this->getTeeth(),
            'contracts' => $this->contracts()->with('client.photo')->get()->map(
                fn ($c) => extract_fields($c, [], [
                    'client' => new PersonWithPhotoResource($c->client)
                ])
            )
        ]);
    }
}
