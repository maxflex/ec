<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SmsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $item = (object) $this->resource;

        return extract_fields($item, [
            'phone', 'status_name', 'status', 'message',
        ], [
            'created_at' => Carbon::parse($item->send_date)->format('Y-m-d H:i:s'),
        ]);
    }
}
