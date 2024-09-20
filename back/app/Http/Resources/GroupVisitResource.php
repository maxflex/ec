<?php

namespace App\Http\Resources;

use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Lesson */
class GroupVisitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $clientLessons = $this->clientLessons()
            ->with('contractVersionProgram.contractVersion.contract.client')
            ->get();

        return extract_fields($this, [
            'dateTime', 'status'
        ], [
            'teacher' => new PersonResource($this->teacher),
            'clientLessons' => $clientLessons->map(fn($cl) => extract_fields($cl, [
                'status', 'is_remote', 'minutes_late'
            ], [
                'client' => new PersonResource($cl->contractVersionProgram->contractVersion->contract->client),
            ]))
        ]);
    }
}
