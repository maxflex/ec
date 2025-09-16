<?php

namespace App\Http\Resources;

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
        $extra = [
            'teachers' => PersonResource::collection($this->teachers),
            'teeth' => $this->getSavedSchedule($this->year),
        ];

        // управление группами клиента, нужно отобразить состояние исполнения договора
        if ($this->relationLoaded('clientGroups')) {
            if ($this->clientGroups->count() === 1) {

                $clientGroup = $this->clientGroups->first();
                $extra['swamp'] = extract_fields($clientGroup->contractVersionProgram, [
                    'status', 'program', 'total_lessons', 'lessons_conducted',
                ], [
                    'id' => $clientGroup->id,
                ]);
            }

            $extra['overlap'] = $this->overlap;
            $extra['is_program_used'] = $this->is_program_used;
        }

        return extract_fields($this, [
            'program', 'client_groups_count', 'zoom', 'lessons_planned',
            'teacher_counts', 'lesson_counts', 'first_lesson_date', 'cabinets',
            'letter', 'project_students_count',
        ], $extra);
    }
}
