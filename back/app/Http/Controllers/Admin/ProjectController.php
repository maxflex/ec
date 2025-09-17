<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Client;
use App\Models\Project;
use Exception;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    protected $filters = [
        'equals' => ['client_id', 'year'],
    ];

    public function index(Request $request)
    {
        $query = Project::latest()->withCount('comments');

        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, ProjectResource::class);
    }

    /**
     * Вкладка управление группами у клиента
     */
    public function fromActualContracts(Request $request)
    {
        $request->validate([
            'id' => ['sometimes', 'exists:projects,id'],
            'client_id' => ['sometimes', 'exists:clients,id'],
        ]);

        if ($request->has('id')) {
            $project = Project::find($request->input('id'));
            $project->unpack();
        } elseif ($request->has('client_id')) {
            $client = Client::find($request->client_id);
            $project = Project::fromActualContracts($client);
        } else {
            $project = Project::fromActualContracts(null);
        }

        $project->user_id = auth()->id();
        $project->toRam();

        return $project->getData();
    }

    public function removeFromGroup(Request $request)
    {
        $request->validate([
            // может быть -1 для несуществующих программ
            'program_id' => ['required', 'numeric'],
        ]);

        $project = Project::fromRam(auth()->id());
        $project->removeFromGroup(intval($request->program_id));
        $project->toRam();

        return $project->getData();
    }

    public function addToGroup(Request $request)
    {
        $request->validate([
            // может быть -1 для несуществующих программ
            'program_id' => ['required', 'numeric'],
            'group_id' => ['required', 'exists:groups,id'],
        ]);

        $project = Project::fromRam(auth()->id());
        $wasAdded = $project->addToGroup(
            intval($request->program_id),
            intval($request->group_id)
        );

        abort_if(! $wasAdded, 422);

        $project->toRam();

        return $project->getData();
    }

    public function removeProgram(Request $request)
    {
        $request->validate([
            'id' => ['required', 'numeric', 'max:-1'],
        ]);

        $project = Project::fromRam(auth()->id());
        $project->removeProgram(intval($request->id));
        $project->toRam();

        return $project->getData();
    }

    public function addPrograms(Request $request)
    {
        $request->validate([
            'contract_id' => ['required', 'numeric'],
            'new_programs' => ['sometimes', 'array'],
        ]);

        $project = Project::fromRam(auth()->id());
        $project->addPrograms($request->new_programs, intval($request->contract_id));
        $project->toRam();

        return $project->getData();
    }

    public function getTeeth()
    {
        return Project::fromRam(auth()->id())->getSchedule();
    }

    public function applyMoveGroups()
    {
        $project = Project::fromRam(auth()->id());
        try {
            $project->applyMoveGroups();
        } catch (Exception $e) {
            abort(422, $e->getMessage());
        }

        return $project->getData();
    }

    public function save(Request $request)
    {
        $request->validate([
            'contract_id' => ['required', 'numeric'],
        ]);

        $contractId = intval($request->contract_id);
        $project = Project::fromRam(auth()->id());

        return new ProjectResource(
            $project->saveProject($contractId)
        );
    }

    /**
     * Загрузить сохранённый проект
     */
    public function show(Project $project)
    {
        $project->loadCount('comments');

        return new ProjectResource($project);
    }

    /**
     * Загрузить проект (из Editor)
     */
    public function load(Project $project)
    {
        $fromRam = Project::fromRam(auth()->id());

        $fromRam->insertPrograms($project);
        $fromRam->toRam();

        return $fromRam->getData();
    }

    /**
     * Создать версию договора на основе проекта
     */
    public function fillContract(Request $request)
    {
        // если ID не передан – создаём из RAM, тогда нам нужно знать
        // к какому договору из RAM будем создавать
        $request->validate([
            'id' => ['sometimes', 'exists:projects,id'],
            'contract_id' => ['sometimes', 'numeric'],
        ]);

        if ($request->has('id')) {
            $project = Project::find($request->id);
        } else {
            $contractId = intval($request->contract_id);
            $project = Project::fromRam(auth()->id());
            $project->contract_id = $contractId < 0 ? null : $contractId;
        }

        return $project->fillContract();
    }

    /**
     * Удалить сохранённый проект
     */
    public function destroy(Project $project)
    {
        $project->comments->each->delete();
        $project->delete();
    }
}
