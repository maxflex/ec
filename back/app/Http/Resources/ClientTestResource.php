<?php

namespace App\Http\Resources;

use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientTestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $fields = ['program', 'name', 'file', 'minutes'];
        $clientId = auth()->user()->entity_id;
        $result = $this->results[$clientId];
        $isFinished = Test::whereId($this->id)->finished($clientId)->exists();
        if ($isFinished) {
            $fields[] = 'questions';
        }
        return extract_fields($this, $fields, [
            'questions_count' => count($this->questions),
            'result' => $result,
            'is_finished' => $isFinished,
        ]);
    }
}
