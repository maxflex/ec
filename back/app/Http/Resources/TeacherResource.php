<?php

namespace App\Http\Resources;

use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Teacher */
class TeacherResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'photo_url' => $this->photo_url,
            'payments' => $this->payments,
            'phones' => PhoneListResource::collection($this->phones),
            'user' => new PersonResource($this->user),
        ]);
    }
}
