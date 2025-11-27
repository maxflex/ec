<?php

namespace App\Http\Resources;

use App\Enums\Quarter;
use App\Models\Client;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @mixin Collection<Grade>
 */
class QuartersGradesSimpleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Collection|Grade[] $grades */
        $grades = $this->resource;

        $first = $grades->first(); // любая запись из группы

        $byQuarter = $grades->keyBy('quarter');

        $quarters = [];
        foreach (Quarter::cases() as $quarter) {
            $grade = $byQuarter->get($quarter->value);

            $quarters[$quarter->value] = [
                'grade' => $grade ? new GradeResource($grade) : null,
                'is_grade_needed' => false,
            ];
        }

        return [
            'year' => $first->year,
            'program' => $first->program,
            'quarters' => $quarters,
            'client' => new PersonResource(Client::find($first->client_id)),
        ];
    }
}
