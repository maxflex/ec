<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'status', 'phones', 'created_at', 'comment'
        ], [
            'responsible_user' => new PersonResource($this->responsibleUser),
            'client' => new PersonResource($this->client),
        ]);
    }
}
