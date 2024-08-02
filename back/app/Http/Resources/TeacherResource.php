<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'teeth' => $this->getTeeth(),
            'phones' => PhoneListResource::collection($this->phones),
        ]);
    }
}
