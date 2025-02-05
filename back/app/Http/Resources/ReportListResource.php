<?php

namespace App\Http\Resources;

use App\Models\Client;
use App\Models\Report;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Report
 */
class ReportListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // кол-во отчетов в плоскости
        $count = Report::where([
            'teacher_id' => $this->teacher_id,
            'client_id' => $this->client_id,
            'year' => $this->year,
            'program' => $this->program,
        ])->count();

        // real report
        if ($this->id) {
            $report = Report::find($this->id);
            return extract_fields($report, [
                'year', 'program', 'status', 'fill', 'price',
                'grade', 'to_check_at', 'requirement',
            ], [
                'lessons_count' => $report->lessons->count(),
                'teacher' => new PersonResource($report->teacher),
                'client' => new PersonResource($report->client),
                'count' => $count - 1,
            ]);
        }
        // fake report
        return extract_fields($this, [
            'year', 'program', 'lessons_count', 'requirement'
        ], [
            'id' => uniqid(),
            'teacher' => new PersonResource(Teacher::find($this->teacher_id)),
            'client' => new PersonResource(Client::find($this->client_id)),
            'count' => $count,
        ]);
    }
}
