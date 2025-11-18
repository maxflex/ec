<?php

namespace App\Http\Resources;

use App\Models\Client;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Client|Teacher */
class PeopleSelectorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'first_name', 'last_name', 'middle_name',
            'directions',
        ], [
            'entity_type' => get_class($this->resource),
            'event_participant' => $this->whenHas('event_participant'),

            /**
             * используется на странице group-message/send
             */
            'phones' => $this->whenLoaded(
                'phones',
                fn () => PhoneResource::collection($this->phones)
            ),
            'representative' => $this->whenLoaded(
                'representative',
                fn () => new PersonWithPhonesResource($this->representative)
            ),
        ]);
    }
}
