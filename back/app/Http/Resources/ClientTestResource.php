<?php

namespace App\Http\Resources;

use App\Models\ClientTest;
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientTestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $extra = [
            'user' => new PersonResource($this->user)
        ];

        if ($this->is_finished) {
            $extra = [
                ...$extra,
                'questions' => $this->questions,
                'answers' => $this->answers
            ];
        }

        if ($this->is_active) {
            $extra = [
                ...$extra,
                'seconds_left' => $this->secondsLeft,
            ];
        }

        return extract_fields($this, [
            'program', 'name', 'is_finished', 'is_active',
            'minutes', 'questions_count', 'file', 'created_at',
        ], $extra);
    }
}
