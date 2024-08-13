<?php

namespace App\Http\Resources;

use App\Models\Group;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Group */
class GroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return extract_fields($this, ['*'], [
            'user' => new PersonResource($this->user),
            'teachers' => $this->getTeachers()->map(
                fn ($id) =>
                new PersonResource(Teacher::find($id))
            ),
            'teeth' => $this->getTeeth($this->year)
        ]);
    }
}
