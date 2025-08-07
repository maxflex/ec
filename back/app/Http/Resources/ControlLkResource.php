<?php

namespace App\Http\Resources;

use App\Enums\LogDevice;
use App\Enums\ReportDelivery;
use App\Enums\ReportStatus;
use App\Models\Client;
use App\Models\ClientParent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Client
 */
class ControlLkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $reportsReadCount = $this->reports
            ->where('delivery', ReportDelivery::read)
            ->count();

        $reportsPublishedCount = $this->reports
            ->where('status', ReportStatus::published)
            ->count();

        $logsCount = 0;
        $tgLogsCount = 0;
        $parentLogsCount = 0;
        $parentTgLogsCount = 0;

        foreach ($this->logs as $log) {
            if ($log->client_parent_id) {
                $parentLogsCount++;
                if ($log->device == LogDevice::telegram) {
                    $parentTgLogsCount++;
                }
            } else {
                $logsCount++;
                if ($log->device == LogDevice::telegram) {
                    $tgLogsCount++;
                }
            }
        }

        return extract_fields($this, [
            'first_name', 'last_name', 'middle_name',
            'last_seen_at', 'directions', 'comments_count',
        ], [
            'logs_count' => $logsCount,
            'tg_logs_count' => $tgLogsCount,
            'phones' => PhoneResource::collection($this->phones),
            'entity_type' => Client::class,
            'parent' => extract_fields($this->parent, [
                'first_name', 'last_name', 'middle_name',
                'last_seen_at',
            ], [
                'entity_type' => ClientParent::class,
                'logs_count' => $parentLogsCount,
                'tg_logs_count' => $parentTgLogsCount,
                'phones' => PhoneResource::collection($this->parent->phones),
                'reports_read_count' => $reportsReadCount,
                'reports_published_count' => $reportsPublishedCount,
            ]),
        ]);
    }
}
