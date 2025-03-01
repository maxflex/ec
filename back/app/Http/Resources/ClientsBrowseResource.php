<?php

namespace App\Http\Resources;

use App\Models\Client;
use App\Models\ClientParent;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientsBrowseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $entityType = $this->entity_type ?? get_class($this->resource);

        $extra = match ($entityType) {
            Client::class => [
                'last_seen_at' => $this->logs()->whereNull('emulation_user_id')->max('created_at'),
                'directions' => $this->directions,
            ],
            ClientParent::class => [
                'last_seen_at' => Log::where('client_parent_id', $this->id)->whereNull('emulation_user_id')->max('created_at'),
                'directions' => $this->client->directions,
            ],
            default => [
                'last_seen_at' => $this->logs()->whereNull('emulation_user_id')->max('created_at'),
            ],
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
