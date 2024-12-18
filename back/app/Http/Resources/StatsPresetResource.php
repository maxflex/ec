<?php

namespace App\Http\Resources;

use App\Models\StatsPreset;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin StatsPreset */
class StatsPresetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'metric' => $this->metric,
            'color' => $this->color,
            'filters' => $this->filters,
            'label' => $this->label,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
