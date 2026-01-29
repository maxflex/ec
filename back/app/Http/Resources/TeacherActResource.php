<?php

namespace App\Http\Resources;

use App\Models\TeacherAct;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TeacherAct
 */
class TeacherActResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            '*',
        ], [
            'user' => new PersonResource($this->user),
        ]);
    }
}
