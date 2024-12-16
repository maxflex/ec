<?php

namespace App\Http\Resources;

use App\Models\PassLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PassLog */
class PassLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'entity', 'comment', 'complaint', 'used_at',
        ]);
    }
}
