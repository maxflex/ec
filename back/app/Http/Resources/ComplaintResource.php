<?php

namespace App\Http\Resources;

use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Complaint */
class ComplaintResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'text', 'client_id', 'teacher_id', 'program',
            'created_at', 'year',
        ], [
            'user' => new PersonResource($this->user),
        ]);
    }
}
