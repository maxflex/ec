<?php

namespace App\Http\Resources;

use App\Models\Instruction;
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
