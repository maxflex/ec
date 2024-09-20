<?php

namespace App\Http\Resources;

use App\Models\ClientLesson;
use Illuminate\Http\Request;

class GradeResource extends GradeListResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $arr = parent::toArray($request);

        foreach ($arr['quarters'] as $quarter => $data) {
            $clientLessons = ClientLesson::query()
                ->where('contract_version_program_id', $arr['contract_version_program_id'])
                ->whereHas('lesson', fn($q) => $q->where('quarter', $quarter))
                ->get();
            $arr['quarters'][$quarter]['client_lessons'] = $clientLessons
                ->map(fn($cl) => extract_fields($cl, [
                    'is_remote', 'minutes_late', 'status', 'scores',
                ], [
                    'lesson' => extract_fields($cl->lesson, [
                        'topic', 'date'
                    ], [
                        'teacher' => new PersonResource($cl->lesson->teacher)
                    ])
                ]));
        }

        return $arr;
    }
}
