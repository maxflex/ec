<?php

namespace App\Http\Resources;

use App\Models\ContractVersionProgram;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ContractVersionProgram */
class ContractVersionProgramResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'program', 'lessons_planned', 'prices',
            'lessons_conducted', 'lessons_to_be_conducted',
            'lessons_total'
        ], [
            'group_id' => $this->group?->id,
        ]);
    }
}
