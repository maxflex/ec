<?php

namespace App\Http\Resources;

use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $versions = $this->versions()->withCount('signs')->get();

        return extract_fields($this, [
            'title', 'text', 'entry_id', 'status',
        ], [
            'versions' => $versions->map(fn ($v) => extract_fields($v, [
                'title', 'created_at', 'signs_count',
            ])),
            'teachers' => Teacher::canLogin()->get()->map(fn ($t) => extract_fields($t, [
                'first_name', 'last_name', 'middle_name', 'photo_url',
            ], [
                'signed_at' => $t->signs()->where('instruction_id', $this->id)->first()?->signed_at,
            ]))
                ->sortBy([
                    ['signed_at', 'desc'],
                    ['last_name', 'asc'],
                    ['first_name', 'asc'],
                ])
                ->values(),
            'signs' => $this->signs()
                ->orderBy('signed_at', 'desc')
                ->get()
                ->map(fn ($s) => extract_fields($s, [
                    'signed_at',
                ], [
                    'teacher' => new PersonWithPhotoResource($s->teacher),
                ])),
        ]);
    }
}
