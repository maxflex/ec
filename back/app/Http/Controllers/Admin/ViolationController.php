<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ViolationListResource;
use App\Http\Resources\ViolationResource;
use App\Models\Violation;
use Illuminate\Http\Request;

class ViolationController extends Controller
{
    protected $filters = [
        'null' => ['client_lesson_id'],
        'equals' => ['is_resolved'],
        'client' => ['client_id'],
    ];

    public function index(Request $request)
    {
        $query = Violation::with([
            'clientLesson.contractVersionProgram.contractVersion.contract.client',
            'lesson.teacher',
            'lesson.group',
        ])->withCount('comments');

        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, ViolationListResource::class);
    }

    public function show(Violation $violation)
    {
        return new ViolationResource($violation);
    }

    public function store(Request $request)
    {
        $violation = new Violation($request->all());
        $violation->user_id = auth()->id();
        $violation->save();

        return new ViolationResource($violation);
    }

    public function update(Violation $violation, Request $request)
    {
        $violation->update($request->all());

        return new ViolationListResource($violation);
    }

    public function destroy(Violation $violation)
    {
        $violation->delete();
    }

    protected function filterClient($query, $clientId)
    {
        $query->whereHas(
            'clientLesson.contractVersionProgram.contractVersion.contract',
            fn ($q) => $q->where('client_id', $clientId)
        );
    }
}
