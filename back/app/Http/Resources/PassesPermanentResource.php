<?php

namespace App\Http\Resources;

use App\Models\Client;
use App\Models\Representative;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PassesPermanentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $entityType = $this->entity_type ?? get_class($this->resource);

        $extra = $entityType !== Client::class ? [] : [
            'directions' => $this->directions,
            'representative' => extract_fields($this->representative, [
                'first_name', 'last_name', 'middle_name',
            ], [
                'entity_type' => Representative::class,
            ]),
        ];

        return extract_fields($this, [
            'first_name', 'last_name', 'middle_name',
        ], $extra);
    }
}
