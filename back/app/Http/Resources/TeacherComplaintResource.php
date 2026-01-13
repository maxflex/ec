<?php

namespace App\Http\Resources;

use App\Models\TeacherComplaint;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TeacherComplaint */
class TeacherComplaintResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'text', 'status', 'created_at',
            'comments_count',
        ], [
            'teacher' => new PersonResource($this->teacher),
        ]);
    }
}
