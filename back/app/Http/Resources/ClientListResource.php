<?php

namespace App\Http\Resources;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Client */
class ClientListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'first_name', 'last_name', 'middle_name', 'directions', 'created_at',
        ], [
            'schedule' => $this->when(
                $request->has('can_login'),
                fn () => $this->getSavedSchedule(current_academic_year())
            ),
        ]);
    }
}
