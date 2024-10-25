<?php

namespace App\Http\Resources;

use App\Models\HeadTeacherReport;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin HeadTeacherReport
 */
class HeadTeacherReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'text', 'created_at', 'month', 'year'
        ], [
            'teacher' => new PersonResource($this->teacher)
        ]);
    }
}
