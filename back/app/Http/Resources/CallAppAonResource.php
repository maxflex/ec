<?php

namespace App\Http\Resources;

use App\Models\Phone;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Phone */
class CallAppAonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $extra = match ($this->entity_type) {
            \App\Models\Request::class => [
                'request_id' => $this->entity_id,
            ],
            default => [
                'entity' => new PersonResource($this->entity),
            ],
        };

        return extract_fields($this, [
            'number', 'comment', 'telegram_id', 'is_telegram_disabled',
        ], $extra);
    }
}
