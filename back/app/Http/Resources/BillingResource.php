<?php

namespace App\Http\Resources;

use App\Enums\Program;
use App\Models\ContractVersion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var ContractVersion $version */
        $version = $this->versions[0];

        // Если есть хотя бы одна программа курсов, открывается оплата по СБП в мобильной версии
        $programs = Program::getAllCourses();
        $hasCoursesProgram = $version->programs->some(fn ($p) => $programs->contains($p->program));

        return extract_fields($this, [
            'year', 'company',
        ], [
            'representative' => new PersonResource($this->client->representative),
            'payments' => extract_fields_array($this->payments, [
                'date', 'sum', 'is_return',
            ]),
            'version' => extract_fields($version, [
                'date',
            ], [
                'has_courses_program' => $hasCoursesProgram,
                'payments' => extract_fields_array($version->payments, [
                    'date', 'sum',
                ]),
            ]),
        ]);
    }
}
