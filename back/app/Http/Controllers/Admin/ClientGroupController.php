<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientGroupResource;
use App\Http\Resources\PersonWithPhotoResource;
use App\Models\ClientGroup;
use App\Models\Group;
use Illuminate\Http\Request;

class ClientGroupController extends Controller
{
    protected $filters = [
        'equals' => ['group_id'],
    ];

    public function index(Request $request)
    {
        $request->validate([
            'group_id' => ['required', 'exists:groups,id'],
        ]);

        $group = Group::find($request->group_id);
        $query = ClientGroup::with('contractVersionProgram.contractVersion.contract.client.photo');
        $this->filter($request, $query);

        $result = collect();
        $students = $query->get();

        foreach ($students as $d) {
            $client = $d->contractVersionProgram->contractVersion->contract->client;

            $result->push(extract_fields($d, [
                'contract_version_program_id',
            ], [
                'teeth' => $client->getSavedSchedule($group->year),
                'client' => new PersonWithPhotoResource($client),
                'project_id' => null,
                'is_removed' => false,
            ]));
        }

        // нужно подмешать "проектных" учеников
        $projectStudents = $group->project_students;
        $isFirstAdded = true;
        foreach ($projectStudents as $student) {
            // ученик добавлен реально + удалён в проекте
            if ($student->is_removed) {
                $result = $result->map(function ($item) use ($student) {
                    if ($item['contract_version_program_id'] === $student->contract_version_program_id) {
                        $item['project_id'] = $student->project_id;
                        $item['group_id'] = $student->group_id;
                        $item['is_removed'] = true;
                    }

                    return $item;
                });
            } else {
                $result->push(extract_fields($student, [
                    'contract_version_program_id', 'project_id', 'group_id', 'is_removed',
                    'real_group_id',
                ], [
                    'is_first_added' => $isFirstAdded,
                    'teeth' => $student->client->getSavedSchedule($group->year),
                    'client' => new PersonWithPhotoResource($student->client),
                ]));
                $isFirstAdded = false;
            }

        }

        return paginate($result);
        //
        // return $this->handleIndexRequest($request, $query, ClientGroupResource::class);
    }

    /**
     * Добавить ученика в группу (теперь массовое)
     */
    public function store(Request $request)
    {
        $request->validate([
            'group_id' => ['required', 'exists:groups,id'],
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:contract_version_programs,id'],
        ]);

        foreach ($request->ids as $id) {
            ClientGroup::create([
                'group_id' => $request->group_id,
                'contract_version_program_id' => $id,
            ]);
        }
    }

    /**
     * Удалить ученика из группы
     */
    public function destroy(ClientGroup $clientGroup)
    {
        $clientGroup->delete();
    }
}
