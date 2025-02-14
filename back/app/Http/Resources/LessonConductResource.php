<?php

namespace App\Http\Resources;

use App\Enums\ClientLessonStatus;
use App\Enums\LessonStatus;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Lesson */
class LessonConductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $students = match ($this->status) {
            // если урок проведён, берём из clientLessons
            LessonStatus::conducted => ClientLessonResource::collection($this->clientLessons),

            // если урок не проведён, берём из clientGroups
            default => $this->group->clientGroups()
                ->get()
                ->map(fn ($cg) => extract_fields($cg, [
                    'contract_version_program_id',
                ], [
                    'client' => new PersonWithPhotoResource(
                        $cg->contractVersionProgram->contractVersion->contract->client
                    ),
                    'status' => ClientLessonStatus::present,
                    'is_remote' => false,
                    'minutes_late' => 10,
                    'comment' => null,
                    'scores' => [],
                ]))
        };

        return extract_fields($this, [
            'status', 'conducted_at', 'topic',
        ], [
            'students' => $students,
        ]);
    }
}
