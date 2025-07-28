<?php

namespace App\Http\Resources;

use App\Models\ScheduleDraft;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ScheduleDraft */
class SavedScheduleDraftResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'created_at', 'contract_id', 'year', 'changes',
        ], [
            'client' => new PersonResource($this->client),
            'user' => new PersonResource($this->user),
        ]);
    }
}
