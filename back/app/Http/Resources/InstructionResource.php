<?php

namespace App\Http\Resources;

use App\Models\Instruction;
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
        if (auth()->user()->isTeacher()) {
            $versions = Instruction::queryForTeacher(auth()->id())
                ->where('entry_id', $this->entry_id)
                ->get();
            $sign = $this->signs()
                ->where('teacher_id', auth()->id())
                ->first();
        } else {
            $versions = $this->versions()->withCount('signs')->get();
        }

        return extract_fields($this, [
            'title', 'text', 'entry_id'
        ], [
            'versions' => $versions->map(fn ($v) => extract_fields($v, [
                'title', 'created_at', 'signed_at', 'signs_count'
            ])),
            'teachers' => $this->when(
                auth()->user()->isAdmin(),
                Teacher::active()->get()->map(fn ($t) => extract_fields($t, [
                    'first_name', 'last_name', 'middle_name', 'photo_url'
                ], [
                    'signed_at' => $t->signs()->where('instruction_id', $this->id)->first()?->signed_at
                ]))
                    ->sortByDesc(fn ($t) => $t['signed_at'] !== null)
                    ->sortBy([
                        ['last_name', 'asc'],
                        ['first_name', 'asc']
                    ])->values()->all()
            ),
            'signs' => $this->when(
                auth()->user()->isAdmin(),
                $this->signs()
                    ->orderBy('signed_at', 'desc')
                    ->get()
                    ->map(fn ($s) => extract_fields($s, [
                        'signed_at'
                    ], [
                        'teacher' => new PersonWithPhotoResource($s->teacher)
                    ]))
            ),
            'signed_at' => $this->when(
                isset($sign),
                fn () => $sign->signed_at
            )
        ]);
    }
}
