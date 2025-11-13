<?php

namespace App\Http\Resources;

use App\Models\Violation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Violation */
class ViolationListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return extract_fields($this, ['*'], [
            'lesson' => extract_fields($this->lesson, [
                'date', 'time', 'time_end',
            ], [
                'group' => extract_fields($this->lesson->group, [
                    'program', 'letter',
                ]),
                'teacher' => new PersonResource($this->lesson->teacher),
            ]),
            'client' => new PersonResource($this->clientLesson?->contractVersionProgram->contractVersion->contract->client),
        ]);
    }
}
