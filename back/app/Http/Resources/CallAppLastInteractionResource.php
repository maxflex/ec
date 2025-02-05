<?php

namespace App\Http\Resources;

use App\Models\Call;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Call */
class CallAppLastInteractionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'type', 'created_at', 'finished_at', 'answered_at',
            'is_missed', 'is_missed_callback'
        ], [
            'user' => new PersonResource($this->user),
        ]);
    }
}
