<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'text', 'created_at', 'entry_id', 'template'
        ], [
            'user' => new PersonWithPhotoResource($this->user),
            'phone' => extract_fields($this->phone, [
                'number'
            ], [
                'entity' => new PersonWithPhotoResource($this->phone->entity)
            ])
        ]);
    }
}
