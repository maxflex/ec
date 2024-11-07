<?php

namespace App\Http\Resources;

use App\Enums\LessonStatus;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Group */
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
            'program', 'zoom', 'client_groups_count'
        ], [
            'lessons_count' => $this->lessons()->where('status', '<>', LessonStatus::cancelled)->where('is_free', 0)->count(),
            'lessons_free_count' => $this->lessons()->where('status', '<>', LessonStatus::cancelled)->where('is_free', 1)->count(),
            'lessons_conducted_count' => $this->lessons()->where('status', LessonStatus::conducted)->where('is_free', 0)->count(),
            'lessons_conducted_free_count' => $this->lessons()->where('status', LessonStatus::conducted)->where('is_free', 1)->count(),
            'teachers' => PersonResource::collection($this->teachers),
            'teeth' => $this->getTeeth($this->year)
        ]);
    }
}
