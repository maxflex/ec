<?php

namespace App\Http\Resources;

use App\Models\Instruction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Instruction */
class InstructionTeacherListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'title', 'created_at', 'is_last_version',
        ], [
            'signed_at' => $this->signs->first()?->signed_at,
        ]);
    }
}
