<?php

namespace App\Http\Resources;

use App\Models\Phone;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Phone
 */
class PhoneResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'number', 'comment', 'telegram_id', 'entity_type', 'entity_id'
        ]);
    }
}
