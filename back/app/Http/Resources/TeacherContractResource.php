<?php

namespace App\Http\Resources;

use App\Models\TeacherContract;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TeacherContract
 */
class TeacherContractResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'problems_count', '*',
        ], [
            'user' => new PersonResource($this->user),
        ]);
    }
}
