<?php

namespace App\Http\Resources;

use App\Models\TeacherAct;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TeacherAct
 */
class TeacherActListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'date', 'total', 'date_from', 'date_to',
            'file',
        ], [
            'teacher' => new PersonResource($this->teacher),
        ]);
    }
}
