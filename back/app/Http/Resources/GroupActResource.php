<?php

namespace App\Http\Resources;

use App\Models\GroupAct;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin GroupAct */
class GroupActResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'group_id', 'teacher_id', 'date', 'date_from', 'date_to',
            'sum', 'lessons', 'created_at'
        ], [
            'user' => new PersonResource($this->user),
            'teacher' => new PersonResource($this->teacher)
        ]);
    }
}
