<?php

namespace App\Http\Resources;

use App\Models\Client;
use App\Models\ClientParent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientsBrowseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $entityType = $this->entity_type ?? get_class($this->resource);

        $extra = match ($entityType) {
            Client::class => [
                'directions' => $this->directions
            ],
            ClientParent::class => [
                'directions' => $this->client->directions
            ],
            default => [],
        };

        return extract_fields($this, [
            'first_name', 'last_name', 'middle_name',
        ], [
            ...$extra,
            'phones' => PhoneResource::collection($this->phones),
            'entity_type' => $entityType,
        ]);
    }
}
