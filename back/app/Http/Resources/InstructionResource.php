<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'title', 'text', 'entry_id'
        ], [
            'versions' => $this->versions->map(fn ($v) => extract_fields($v, [
                'title', 'created_at'
            ]))
        ]);
    }
}
