<?php

namespace App\Http\Resources;

use App\Models\Client;
use App\Models\Contract;
use App\Models\ContractVersion;
use App\Models\ContractVersionProgram;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Client */
class ControlGradesResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'first_name', 'last_name', 'middle_name',
        ], [
            'grades' => $this->grades->pluck('grade'),
            'reports' => $this->reports->pluck('grade'),
            'mark_sheet' => collect($this->mark_sheet)->values(),
            'client_lessons' => $this->contracts
                ->map(fn (Contract $c) => $c->activeVersion)
                ->map(fn (ContractVersion $cv) => $cv->programs)
                ->flatten()
                ->map(fn (ContractVersionProgram $cvp) => $cvp->clientLessons)
                ->flatten()
                ->pluck('scores')
                ->flatten()
                ->pluck('score'),
        ]);
    }
}
