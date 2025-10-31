<?php

namespace App\Http\Resources;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Project */
class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'created_at', 'contract_id', 'year', 'changes',
            'contract_id', 'is_archived', 'comments_count',
            'name',
        ], [
            'has_problems' => $this->when(
                $request->boolean('with_problems'),
                fn () => $this->has_problems,
            ),
            'client' => new ClientResource($this->client),
            'user' => new PersonResource($this->user),
        ]);
    }
}
