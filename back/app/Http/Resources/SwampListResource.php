<?php

namespace App\Http\Resources;

use App\Models\Client;
use App\Models\ContractVersionProgram;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ContractVersionProgram
 */
class SwampListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $group = $this->clientGroup?->group;
        if ($group) {
            $group->loadCount('clientGroups');
        }

        return extract_fields($this, [
            'contract_id', 'year', 'status', 'program',
            'total_lessons', 'lessons_conducted',
        ], [
            'client_group_id' => $this->clientGroup?->id,
            'group' => new GroupListResource($group),
            'client' => new PersonResource(Client::find($this->client_id)),
        ]);
    }
}
