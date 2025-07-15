<?php

namespace App\Http\Resources;

use App\Models\ClientReview;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ClientReview */
class ClientReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'text', 'client_id', 'teacher_id', 'program',
            'created_at', 'rating',
        ], [
            'user' => new PersonResource($this->user),
        ]);
    }
}
