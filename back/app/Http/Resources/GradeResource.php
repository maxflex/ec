<?php

namespace App\Http\Resources;

use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Grade
 */
class GradeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'grade', 'program', 'created_at'
        ], [
            'teacher' => new PersonResource($this->teacher)
        ]);
    }
}
