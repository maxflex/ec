<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $version = $this->versions[0];

        return extract_fields($this, [
            'year', 'company',
        ], [
            'representative' => new PersonResource($this->client->representative),
            'payments' => extract_fields_array($this->payments, [
                'date', 'sum', 'is_return',
            ]),
            'version' => extract_fields($version, [
                'date',
            ], [
                'payments' => extract_fields_array($version->payments, [
                    'date', 'sum',
                ]),
            ]),
        ]);
    }
}
