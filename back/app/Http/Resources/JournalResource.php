<?php

namespace App\Http\Resources;

use App\Models\ClientLesson;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ClientLesson
 */
class JournalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $program = $this->program ?? $this->lesson->group->program;

        return extract_fields($this, [
            'status', 'scores', 'minutes_late', 'comment',
        ], [
            'program' => $program,
            'lesson' => extract_fields($this->lesson, [
                'date', 'topic', 'homework', 'files', 'quarter',
                'time', 'time_end',
            ], [
                'teacher' => new PersonResource($this->lesson->teacher),
            ]),
        ]);
    }
}
