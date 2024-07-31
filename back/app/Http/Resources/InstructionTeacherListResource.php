<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructionTeacherListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $signedAt = $this->signs()->where('teacher_id', auth()->id())->value('signed_at');
        return extract_fields($this, [
            'title', 'created_at', 'is_last_version'
        ], [
            'signed_at' => $signedAt,
        ]);
    }
}
