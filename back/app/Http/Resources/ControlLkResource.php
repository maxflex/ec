<?php

namespace App\Http\Resources;

use App\Enums\LogDevice;
use App\Enums\ReportDelivery;
use App\Enums\ReportStatus;
use App\Models\Client;
use App\Models\Representative;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Client
 */
class ControlLkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return extract_fields($this, [
            'first_name', 'last_name', 'middle_name',
            'last_seen_at', 'directions', 'comments_count',
        ], [
            'logs_count' => $this->logs->count(),
            'tg_logs_count' => $this->logs->where('device', LogDevice::telegram)->count(),
            'phones' => PhoneResource::collection($this->phones),
            'entity_type' => Client::class,
            'representative' => extract_fields($this->representative, [
                'first_name', 'last_name', 'middle_name',
                'last_seen_at',
            ], [
                'entity_type' => Representative::class,
                'logs_count' => $this->representative->logs->count(),
                'tg_logs_count' => $this->representative->logs->where('device', LogDevice::telegram)->count(),
                'phones' => PhoneResource::collection($this->representative->phones),
                'reports_read_count' => $this->reports->where('delivery', ReportDelivery::read)->count(),
                'reports_published_count' => $this->reports->where('status', ReportStatus::published)->count(),
            ]),
        ]);
    }
}
