<?php

namespace App\Http\Resources;

use App\Models\Client;
use App\Models\ClientReview;
use App\Models\ExamScore;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

/**
 * @mixin ClientReview
 */
class ClientReviewListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = DB::table('client_lessons', 'cl')
            ->join('contract_version_programs as cvp', 'cvp.id', '=', 'cl.contract_version_program_id')
            ->join('contract_versions as cv', 'cv.id', '=', 'cvp.contract_version_id')
            ->join('contracts as c', 'c.id', '=', 'cv.contract_id')
            ->join('lessons as l', 'l.id', '=', 'cl.lesson_id')
            ->selectRaw('
                COUNT(DISTINCT cl.id) as lessons_count,
                GROUP_CONCAT(DISTINCT c.year) as years
            ')
            ->where('c.client_id', $this->client_id)
            ->where('l.teacher_id', $this->teacher_id)
            ->where('cvp.program', $this->program)
            ->groupByRaw('c.client_id, l.teacher_id, cvp.program')
            ->first();

        $lessonsCount = $data->lessons_count;
        $years = array_map('intval', explode(',', $data->years));

        return extract_fields($this, ['*'], [
            'id' => $this->id ?? uniqid(),
            'is_marked' => (bool) $this->is_marked,
            'lessons_count' => $lessonsCount,
            'years' => $years,
            'teacher' => new PersonResource(Teacher::find($this->teacher_id)),
            'client' => new PersonResource(Client::find($this->client_id)),
            'exam_scores' => ExamScore::where('client_id', $this->client_id)->select(
                'id', 'score', 'max_score', 'exam'
            )->get(),
        ]);
    }
}
