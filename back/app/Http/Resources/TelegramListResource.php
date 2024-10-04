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
            'send_to', 'text', 'created_at', 'scheduled_at',
            'is_confirmable', 'status'
        ], [
            'recipients' => TelegramList::getPeople($this->recipients),
            'event' => $this->when(
                !!$this->event_id,
                fn() => extract_fields($this->event, ['name'])
            ),
            'results' => $this->getResults()
        ]);
    }
}
