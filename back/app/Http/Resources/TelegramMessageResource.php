<?php

namespace App\Http\Resources;

use App\Models\TelegramMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TelegramMessage
 */
class TelegramMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'text', 'created_at', 'list_id', 'template',
            'number', 'telegram_id', 'entity_type'
        ], [
            'entity' => new PersonResource($this->entity)
        ]);
    }
}
