<?php

namespace App\Http\Resources;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Project */
class SavedProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'created_at', 'contract_id', 'year', 'changes',
            'contract_id', 'is_archived', 'comment',
        ], [
            'has_problems_in_list' => $this->when(
                $request->boolean('has_problems_in_list'),
                fn () => $this->has_problems_in_list
            ),
            'client' => new PersonResource($this->client),
            'user' => new PersonResource($this->user),
        ]);
    }
}
