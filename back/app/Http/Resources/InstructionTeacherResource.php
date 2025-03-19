<?php

namespace App\Http\Resources;

use App\Models\Instruction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructionTeacherResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $versions = Instruction::query()
            ->published()
            ->where('entry_id', $this->entry_id)
            ->get()
            ->sortBy('created_at')
            ->values();

        return extract_fields($this, [
            'title', 'text', 'is_last_version',
        ], [
            'versions' => $versions->map(fn ($v) => extract_fields($v, [
                'title', 'created_at', 'is_last_version',
            ], [
                'signed_at' => $v->getSignedAt(auth()->id()),
            ])),
            'is_first_version' => $this->getIsFirstVersion(auth()->id()),
            'signed_at' => $this->getSignedAt(auth()->id()),
        ]);
    }
}
