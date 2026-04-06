<?php

namespace App\Http\Resources;

use App\Models\Call;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Call */
class CallResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'entry_id', 'type', 'number', 'created_at', 'finished_at', 'answered_at',
            'is_missed', 'is_missed_callback', 'has_recording', 'transcript',
            'summary', 'analysis_1', 'analysis_2', 'instruction', 'caller_type',
        ], [
            'user' => new PersonResource($this->user),
            'aon' => Call::aon($this->number),
        ]);
    }
}
