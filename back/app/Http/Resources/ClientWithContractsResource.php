<?php

namespace App\Http\Resources;

use App\Models\Client;
use Illuminate\Http\Request;

/** @mixin Client */
class ClientWithContractsResource extends PersonResource
{
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'contract_versions' => ContractVersionResource::collection(
                $this->contracts->sortByDesc('id')->values()->map(fn($c) => $c->active_version)
            ),
        ]);
    }
}
