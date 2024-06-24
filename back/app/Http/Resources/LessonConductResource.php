<?php

namespace App\Http\Resources;

use App\Enums\ContractLessonStatus;
use App\Enums\LessonStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonConductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $contracts = $this->status === LessonStatus::conducted
            ? $this->contractLessons()->get()->map(
                fn ($cl) => extract_fields($cl, [
                    'is_remote', 'minutes_late', 'status'
                ], [
                    'client' => new PersonWithPhotoResource($cl->contract->client)
                ])
            )
            : $this->group->contracts()
            ->with('client')
            ->get()
            ->map(fn ($c) => extract_fields($c, [], [
                'client' => new PersonWithPhotoResource($c->client),
                'status' => ContractLessonStatus::present,
                'is_remote' => false,
                'minutes_late' => 10,
            ]));

        return extract_fields($this, ['status'], ['contracts' => $contracts]);
    }
}
