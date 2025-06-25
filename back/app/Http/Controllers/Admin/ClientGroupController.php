<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientGroupResource;
use App\Models\ClientGroup;
use App\Models\Contract;
use App\Models\Group;
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
     * Добавить ученика в группу
     */
    public function store(Request $request)
    {
        // новая версия добавления в группу через "управление группами"
        if ($request->has('client_id')) {
            $group = Group::find($request->group_id);
            $contracts = Contract::query()
                ->where('client_id', $request->client_id)
                ->where('year', $group->year)
                ->with(
                    'versions',
                    fn ($q) => $q
                        ->where('is_active', true)
                        ->with('programs')
                )->get();

            // находим программу, по которой можем добавить в эту группу
            $programs = $contracts->map(fn ($c) => $c->active_version->programs)->flatten();
            $program = $programs->where('program', $group->program)->first();

            abort_unless($program, 422, sprintf(
                'В договоре нет нужной программы <b>%s</b>',
                $group->program->getName(),
            ));
            abort_if($program->clientGroup, 422, sprintf(
                'По программе <b>%s</b> ученик уже учится в группе <b>%d</b>',
                $program->program->getName(),
                $program->clientGroup?->group_id
            ));

            $program->clientGroup()->create([
                'group_id' => $group->id,
            ]);

            return ['status' => 'ok'];
        }

        $clientGroup = ClientGroup::create($request->all());

        return new ClientGroupResource($clientGroup);
    }

    /**
     * Удалить ученика из группы
     */
    public function destroy(ClientGroup $clientGroup)
    {
        $clientGroup->delete();
    }
}
