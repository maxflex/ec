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
        return extract_fields($this, [
            'first_name', 'last_name', 'middle_name', 'branches',
            'birthdate', 'head_teacher_id', 'photo_url'
        ], [
            'user' => new PersonResource($this->user),
            'parent' => new ParentResource($this->parent),
            'phones' => PhoneListResource::collection($this->phones),

            // remove
            'groups' => GroupResource::collection($this->groups),
            'swamps' => $this->swamps,
            'tests' => ClientTestResource::collection($this->tests),
        ]);
        // return array_merge(parent::toArray($request), [
        //     'photo_url' => $this->photoUrl,
        //     'contracts' => ContractResource::collection($this->contracts),
        //     'groups' => GroupResource::collection($this->groups),
        //     'swamps' => $this->swamps,
        //     'parent' => new ParentResource($this->parent),
        //     'tests' => ClientTestResource::collection($this->tests),
        //     'head_teacher' => new PersonResource($this->headTeacher),
        //     'phones' => $this->phones,
        //     'requests' => $this->requests,
        // ]);
    }
}
