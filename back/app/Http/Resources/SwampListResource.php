<?php

namespace App\Http\Resources;

use App\Models\Client;
use App\Models\ContractVersionProgram;
use App\Models\Group;
use App\Models\ScheduleDraft;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ContractVersionProgram
 */
class SwampListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $group = $this->clientGroup?->group;

        if ($group) {
            $group->loadCount('clientGroups');
        }

        $scheduleDraft = ScheduleDraft::query()
            ->where('client_id', $this->client_id)
            ->where('contract_id', $this->contract_id)
            ->get()
            ->filter(fn (ScheduleDraft $e) => ! $e->is_archived)
            ->first();

        $changesType = null;
        if ($scheduleDraft) {
            $draftProgram = $scheduleDraft->programs->firstWhere('id', $this->id);

            $draftGroup = $draftProgram && $draftProgram['group_id']
                ? Group::find($draftProgram['group_id'])
                : null;

            if ($draftGroup?->id !== $group?->id) {
                // добавлено в проекте
                if (! $group && $draftGroup) {
                    $changesType = 'added';
                }
                // убрано в проекте
                elseif (! $draftGroup && $group) {
                    $changesType = 'removed';
                } elseif ($group && $draftGroup && $group->id !== $draftGroup->id) {
                    $changesType = 'changed';
                }
            }
        }

        return extract_fields($this, [
            'contract_id', 'year', 'status', 'program',
            'total_lessons', 'lessons_conducted',
        ], [
            'client_group_id' => $this->clientGroup?->id,
            'group' => new GroupListResource($group),
            'client' => new PersonResource(Client::find($this->client_id)),

            // проектные изменения
            'changes' => $changesType === null ? null : [
                'schedule_draft_id' => $scheduleDraft->id,
                'type' => $changesType,
                'group' => new GroupListResource($draftGroup),
            ],
        ]);
    }
}
