<?php

namespace App\Http\Resources;

use App\Models\ClientParent;
use App\Models\Log;
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
            'phones' => PhoneResource::collection($this->phones),
            'last_seen_at' => Log::where('client_parent_id', $this->id)->max('created_at'),
        ]);
    }
}
