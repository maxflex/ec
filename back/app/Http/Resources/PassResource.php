<?php

namespace App\Http\Resources;

use App\Models\Pass;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Pass */
class PassResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'comment', 'type', 'date', 'created_at',
            'used_at', 'is_expired',
        ], [
            'user' => new PersonResource($this->user),
            'request' => $this->when(
                $this->request !== null,
                fn () => extract_fields($this->request, ['direction'])
            ),
        ]);

    }
}
