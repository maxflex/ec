<?php

namespace App\Http\Resources;

use App\Models\Client;
use App\Models\ContractVersionProgram;
use App\Models\Group;
use App\Models\Project;
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

        $project = Project::query()
            ->where('client_id', $this->client_id)
            ->where('contract_id', $this->contract_id)
            ->get()
            ->filter(fn (Project $e) => ! $e->is_archived)
            ->first();

        $changesType = null;
        if ($project) {
            $projectProgram = $project->programs->firstWhere('id', $this->id);

            $projectGroup = $projectProgram && $projectProgram['group_id']
                ? Group::find($projectProgram['group_id'])
                : null;

            if ($projectGroup?->id !== $group?->id) {
                // добавлено в проекте
                if (! $group && $projectGroup) {
                    $changesType = 'added';
                }
                // убрано в проекте
                elseif (! $projectGroup && $group) {
                    $changesType = 'removed';
                } elseif ($group && $projectGroup && $group->id !== $projectGroup->id) {
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
                'project_id' => $project->id,
                'type' => $changesType,
                'group_id' => $projectGroup?->id,
            ],
        ]);
    }
}
