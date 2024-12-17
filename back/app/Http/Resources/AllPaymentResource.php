<?php

namespace App\Http\Resources;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllPaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return extract_fields($this, ['*'], [
            'client' => new PersonResource(Client::find($this->client_id)),
        ]);
    }
}
