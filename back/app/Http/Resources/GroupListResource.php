<?php

namespace App\Http\Resources;

use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'program', 'zoom', 'lessons_count', 'group_contracts_count'
        ], [
            'teachers' => $this->getTeachers()->map(
                fn ($id) =>
                new PersonResource(Teacher::find($id))
            ),
            'teeth' => $this->getTeeth()
        ]);
    }
}
