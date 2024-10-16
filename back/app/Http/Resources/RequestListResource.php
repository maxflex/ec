<?php

namespace App\Http\Resources;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Request
 */
class RequestListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $client = $this->client;
        $phones = $this->phones;
        $numbers = $phones->pluck('number');

        return extract_fields($this, [
            'status', 'created_at', 'comment', 'comments_count',
            'direction', 'user_id'
        ], [
            'phones' => PhoneListResource::collection($phones),
            'responsible_user' => new PersonResource($this->responsibleUser),
            'client' => new PersonResource($client),
            'passes' => PassResource::collection($this->passes),
            'candidates_count' => Client::where(fn($q) => $q
                ->whereHas('phones', fn($q) => $q->whereIn('number', $numbers))
                ->orWhereHas('parent.phones', fn($q) => $q->whereIn('number', $numbers))
            )->where('id', '<>', $client?->id)->count()
        ]);
    }
}
