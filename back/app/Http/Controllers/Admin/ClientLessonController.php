<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientLessonResource;
use App\Models\ClientLesson;
use Illuminate\Http\Request;

class ClientLessonController extends Controller
{
    protected $filters = [
        'equals' => ['teacher_id', 'client_id'],
    ];

    // используется только для получения доступных программ в ClientComplaint/Dialog
    public function index(Request $request)
    {
        $query = ClientLesson::query()
            ->join('lessons as l', 'l.id', '=', 'client_lessons.lesson_id')
            ->join('contract_version_programs as cvp', 'cvp.id', '=', 'client_lessons.contract_version_program_id')
            ->join('contract_versions as cv', 'cv.id', '=', 'cvp.contract_version_id')
            ->join('contracts as c', 'c.id', '=', 'cv.contract_id')
            ->selectRaw('client_lessons.*, cvp.program');

        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query);
    }

    public function show(ClientLesson $clientLesson)
    {
        return new ClientLessonResource($clientLesson);
    }

    public function update(ClientLesson $clientLesson, Request $request)
    {
        $clientLesson->update($request->all());

        return new ClientLessonResource($clientLesson);
    }

    public function destroy(ClientLesson $clientLesson)
    {
        $clientLesson->delete();
    }
}
