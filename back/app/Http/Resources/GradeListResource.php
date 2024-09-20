<?php

namespace App\Http\Resources;

use App\Enums\LessonStatus;
use App\Enums\Quarter;
use App\Models\Client;
use App\Models\ClientGroup;
use App\Models\Grade;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GradeListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $grades = Grade::query()
            ->where('client_id', $this->client_id)
            ->where('year', $this->year)
            ->where('program', $this->program)
            ->pluck('grade', 'quarter');

        foreach (Quarter::cases() as $quarter) {
            $currentGroupId = ClientGroup::query()
                ->where('contract_version_program_id', $this->contract_version_program_id)
                ->value('group_id');
            $q = $quarter->value;
            $conducted = $this->{$q . '_cnt'} ?? 0;
            $planned = $quarter === Quarter::final ? 0 : Lesson::query()
                ->where('group_id', $currentGroupId)
                ->where('quarter', $q)
                ->where('status', LessonStatus::planned)
                ->count();
            $quarters[$q] = [
                'grade' => $grades[$q] ?? null,
                'conducted' => $conducted,
                'total' => $conducted + $planned,
            ];
        }
        return extract_fields($this, [
            'year', 'program', 'contract_version_program_id'
        ], [
            'quarters' => $quarters,
            'client' => new PersonResource(Client::find($this->client_id))
        ]);
    }
}
