<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientGroupResource;
use App\Models\ClientGroup;
use Illuminate\Http\Request;

class ClientGroupController extends Controller
{
    protected $filters = [
        'equals' => ['group_id'],
    ];

    public function index(Request $request)
    {
        $query = ClientGroup::with('contractVersionProgram.contractVersion.contract.client.photo');
        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, ClientGroupResource::class);
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
