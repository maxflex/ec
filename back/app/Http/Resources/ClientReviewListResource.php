<?php

namespace App\Http\Resources;

use App\Models\ClientReview;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ClientReview
 */
class ClientReviewListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $years = array_map('intval', explode(',', $this->years));

        // real
        if ($this->id) {
            return extract_fields($this, [
                'program', 'rating', 'created_at', 'lessons_count', 'text',
            ], [
                'years' => $years,
                'teacher' => new PersonResource($this->teacher),
                'client' => new PersonResource($this->client),
            ]);
        }

        // fake
        return extract_fields($this, [
            'program', 'lessons_count',
        ], [
            'id' => uniqid(),
            'years' => $years,
            'teacher' => new PersonResource($this->teacher),
            'client' => new PersonResource($this->client),
        ]);
    }
}
