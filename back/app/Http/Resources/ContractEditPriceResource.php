<?php

namespace App\Http\Resources;

use App\Models\ContractVersionProgram;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ContractVersionProgram */
class ContractEditPriceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return extract_fields($this, ['program'], [
            'contract' => extract_fields($this->contractVersion->contract, [
                'year', 'company'
            ])
        ]);
    }
}
