<?php

namespace App\Http\Resources;

use App\Models\ClientTest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ClientTest */
class ClientTestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $extra = [
            'file' => $this->file,
            'question_counts' => collect($this->questions)->map(fn ($q) => count($q->answers)),
            'client' => new PersonResource($this->client),
        ];

        if ($this->is_finished) {
            $extra = [
                ...$extra,
                'questions' => $this->questions,
                'answers' => $this->answers,
                'results' => $this->results,
            ];
        }

        // у админов для отображения теста всегда подгружаем вопросы
        if (auth()->user() instanceof User) {
            $extra = [
                ...$extra,
                'questions' => $this->questions,
            ];
        }

        if ($this->is_active) {
            $extra = [
                ...$extra,
                'seconds_left' => $this->seconds_left,
            ];
        }

        return extract_fields($this, [
            'name', 'is_finished', 'is_active',
            'minutes', 'questions_count', 'created_at',
            'finished_at', 'started_at', 'description',
        ], $extra);
    }
}
