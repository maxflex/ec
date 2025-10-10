<?php

namespace App\Http\Resources;

use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Contract */
class ContractBalanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'company',
        ], [
            'client' => new PersonResource($this->client),
            'comments_count' => $this->client->comments_count,
            'active_version_sum' => $this->active_version->sum,
            'latest_payment_date' => $this->payments->max('date'),
            ...$this->balances,
        ]);
    }
}
