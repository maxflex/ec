<?php

namespace App\Http\Resources;

use App\Enums\ContractVersionProgramStatus;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Client */
class PeopleSelectorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'first_name', 'last_name', 'middle_name',
            'directions',
        ], [
            'years' => $this->contracts
                ->where('year', '>=', current_academic_year())
                ->filter(
                    fn ($c) => $c->active_version->programs->some(
                        fn ($p) => in_array($p->status, ContractVersionProgramStatus::getActiveStatuses())
                    ))
                ->pluck('year')
                ->unique()
                ->all(),
        ]);
    }
}
