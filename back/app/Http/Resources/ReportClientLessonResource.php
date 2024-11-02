<?php

namespace App\Http\Resources;

use App\Models\ClientLesson;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ClientLesson
 */
class ReportClientLessonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'status', 'minutes_late', 'is_remote', 'scores'
        ], [
            'lesson' => extract_fields($this->lesson, [
                'date', 'topic'
            ])
        ]);
    }
}
