<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonWithPhonesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'first_name', 'last_name', 'middle_name'
        ], [
            'phones' => PhoneResource::collection($this->phones),
        ]);
    }
}
