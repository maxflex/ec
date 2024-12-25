<?php

namespace App\Http\Resources;

use App\Models\WebReview;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WebReview */
class WebReviewPubResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'text', 'signature', 'rating'
        ], [
            'client' => new PersonWithPhotoResource($this->client),
        ]);
    }
}
