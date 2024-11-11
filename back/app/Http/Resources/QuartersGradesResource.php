<?php

namespace App\Http\Resources;

use App\Enums\LessonStatus;
use App\Enums\Quarter;
use App\Models\Client;
use App\Models\ClientGroup;
use App\Models\ClientLesson;
use App\Models\Grade;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Grade
 */
class QuartersGradesResource extends JsonResource
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
            ->get();

        $quarters = [];
        foreach (Quarter::cases() as $quarter) {
            $currentGroupId = ClientGroup::query()
                ->where('contract_version_program_id', $this->contract_version_program_id)
                ->value('group_id');

            $grade = $grades->where('quarter', $quarter->value)->first();

            // таким образом, в Quarter::final будут значения Quarter::q4
            // (значения от предыдущей итерации цикла)
            if ($quarter !== Quarter::final) {
                $noPlannedLessons = !Lesson::query()
                    ->where('group_id', $currentGroupId)
                    ->where('quarter', $quarter)
                    ->where('status', LessonStatus::planned)
                    ->exists();

                $conductedLessonsCount = $this->{$quarter->value . '_conducted_lessons_count'};

                $isGradeNeeded = $grade === null && $noPlannedLessons && $conductedLessonsCount > 0;
            }


            $quarterData = [
                'grade' => new GradeResource($grade),
                'is_grade_needed' => $isGradeNeeded,
            ];

            // подгрузить client_lessons?
            if ($request->input('with') === 'client_lessons') {
                // final приравниваем к 4 четверти
                if ($quarter === Quarter::final) {
                    $quarterData['last_teacher_id'] = $quarters[Quarter::q4->value]['last_teacher_id'];
                } else {
                    $clientLessons = ClientLesson::query()
                        ->where('contract_version_program_id', $this->contract_version_program_id)
                        ->whereHas('lesson', fn($q) => $q->where('quarter', $quarter))
                        ->get()
                        ->sortBy(fn($cl) => $cl->lesson->dateTime);

                    $quarterData['client_lessons'] = JournalResource::collection($clientLessons);

                    // если подгружаем client_lessons, то это Show страница
                    // тут же нужен last_teacher_id
                    $quarterData['last_teacher_id'] = $clientLessons->last()?->lesson->teacher_id;
                }
            }

            $quarters[$quarter->value] = $quarterData;
        }

        return extract_fields($this, [
            'year', 'program', 'contract_version_program_id'
        ], [
            'quarters' => $quarters,
            'client' => new PersonResource(Client::find($this->client_id))
        ]);
    }
}
