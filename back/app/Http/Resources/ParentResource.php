<?php

namespace App\Http\Resources;

use App\Models\ClientParent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ClientParent */
class ParentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return extract_fields($this, ['*'], [
            'phones' => PhoneListResource::collection($this->phones)
        ]);
    }
}
