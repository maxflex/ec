<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'contracts' => ContractResource::collection($this->contracts),
            'groups' => GroupResource::collection($this->groups),
            'swamps' => $this->swamps,
            'parent' => new ClientParentResource($this->parent),
            'tests' => ClientTestResource::collection($this->tests),
            'head_teacher' => new PersonResource($this->headTeacher),
            'phones' => $this->phones,
            'requests' => $this->requests,
        ]);
    }
}
