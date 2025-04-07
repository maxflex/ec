<?php

namespace App\Http\Resources;

use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Log */
class LogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'type', 'table', 'created_at', 'ip',
            'row_id', 'data', 'is_mobile',
        ], [
            'entity' => new PersonResource(
                $this->clientParent ?? $this->entity
            ),
            'emulation_user' => new PersonResource($this->emulationUser),
        ]);
    }
}
