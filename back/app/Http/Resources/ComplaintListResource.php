<?php

namespace App\Http\Resources;

use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Complaint */
class ComplaintListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'text', 'program', 'created_at', 'comments_count',
        ], [
            'teacher' => new PersonResource($this->teacher),
            'client' => new PersonResource($this->client),
        ]);
    }
}
