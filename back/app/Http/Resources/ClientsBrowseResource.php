<?php

namespace App\Http\Resources;

use App\Enums\LogDevice;
use App\Enums\ReportDelivery;
use App\Enums\ReportStatus;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Client
 */
class ClientsBrowseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $reportsReadCount = $this->reports
            ->where('delivery', ReportDelivery::read)
            ->where('year', current_academic_year())
            ->count();

        $reportsPublishedCount = $this->reports
            ->where('status', ReportStatus::published)
            ->where('year', current_academic_year())
            ->count();

        $logsCount = $this->logs->whereNull('client_parent_id')->count();
        $tgLogsCount = $this->logs->whereNull('client_parent_id')->where('device', LogDevice::telegram)->count();
        $parentLogsCount = $this->logs->where('client_parent_id', $this->parent->id)->count();
        $parentTgLogsCount = $this->logs->where('client_parent_id', $this->parent->id)->where('device', LogDevice::telegram)->count();

        return extract_fields($this, [
            'first_name', 'last_name', 'middle_name',
            'last_seen_at',
        ], [
            'logs_count' => $logsCount,
            'tg_logs_count' => $tgLogsCount,
            'phones' => PhoneResource::collection($this->phones),
            'parent' => extract_fields($this->parent, [
                'first_name', 'last_name', 'middle_name',
                'last_seen_at',
            ], [
                'logs_count' => $parentLogsCount,
                'tg_logs_count' => $parentTgLogsCount,
                'phones' => PhoneResource::collection($this->parent->phones),
                'reports_read_count' => $reportsReadCount,
                'reports_published_count' => $reportsPublishedCount,
            ]),
        ]);
    }
}
