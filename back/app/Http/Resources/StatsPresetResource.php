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
        return extract_fields($this, [
            'metric', 'color', 'label', 'filters'
        ]);
    }
}
