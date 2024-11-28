<?php

namespace App\Http\Resources;

use App\Models\Client;
use App\Models\ScholarshipScore;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ScholarshipScore
 */
class ScholarshipScoreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $score = isset($this->id) ? $this : ScholarshipScore::where([
            ['year', $this->year],
            ['month', $this->month],
            ['client_id', $this->client_id],
            ['teacher_id', $this->teacher_id],
            ['program', $this->program],
        ])->first();
        return extract_fields($this, [
            'year',
            'program',
            'lessons_count',
            'month',
            'client_id',
            'teacher_id',
        ], [
            'client' => new PersonResource(Client::find($this->client_id)),
            'score' => $score ? $score->score : null,
            'id' => $score ? $score->id : null,
        ]);
    }
}
