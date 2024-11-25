<?php

namespace App\Http\Resources;

use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructionListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'title', 'versions_count', 'signs_count', 'created_at',
            'is_published'
        ], [
            'signs_needed' => Teacher::canLogin()->count(),
            'signed_at' => $this->whenLoaded(
                'signs',
                fn () => $this->signs->first()?->signed_at,
            )
        ]);
    }
}
