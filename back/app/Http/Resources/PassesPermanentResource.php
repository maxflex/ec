<?php

namespace App\Http\Resources;

use App\Models\Client;
use App\Models\ClientParent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PassesPermanentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $entityType = $this->entity_type ?? get_class($this->resource);

        $extra = $entityType !== Client::class ? [] : [
            'directions' => $this->directions,
            'parent' => extract_fields($this->parent, [
                'first_name', 'last_name', 'middle_name',
            ], [
                'entity_type' => ClientParent::class,
            ]),
        ];

        return extract_fields($this, [
            'first_name', 'last_name', 'middle_name',
        ], $extra);
    }
}
