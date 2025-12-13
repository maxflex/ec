<?php

namespace App\Http\Resources;

use App\Models\TelegramList;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TelegramList */
class TelegramListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'send_to', 'text', 'created_at', 'scheduled_at', 'status',
        ], [
            'user' => new PersonResource($this->user),
            'recipients' => $this->when(
                $request->has('recipients'),
                $this->unpackRecipients(),
            ),
            'event' => $this->when(
                (bool) $this->event_id,
                fn () => extract_fields($this->event, ['name'])
            ),
            'result' => $this->getResult(),
        ]);
    }
}
