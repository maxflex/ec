<?php

namespace App\Http\Resources;

use App\Models\TeacherContract;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TeacherContract
 */
class TeacherContractListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'date', 'data', 'file', 'is_active', 'seq',
        ], [
            'teacher' => new PersonResource($this->teacher),
        ]);
    }
}
