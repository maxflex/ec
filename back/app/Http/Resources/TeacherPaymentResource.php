<?php

namespace App\Http\Resources;

use App\Models\TeacherPayment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TeacherPayment
 */
class TeacherPaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return extract_fields($this, ['*'], [
            'user' => new PersonResource($this->user),
            'teacher' => new PersonResource($this->teacher),
        ]);
    }
}
