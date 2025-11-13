<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ComplaintRequest;
use App\Http\Resources\ComplaintListResource;
use App\Http\Resources\ComplaintResource;
use App\Models\ClientLesson;
use App\Models\Complaint;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    protected $filters = [
        'equals' => ['client_id', 'teacher_id', 'year'],
        'findInSet' => ['program'],
    ];

    public function index(Request $request)
    {
        $query = Complaint::withCount('comments')->latest();
        $this->filter($request, $query);

        return $this->handleIndexRequest(
            $request,
            $query,
            ComplaintListResource::class
        );
    }

    public function store(ComplaintRequest $request)
    {
        $complaint = auth()->user()->clientComplaints()->create($request->all());

        return new ComplaintListResource($complaint);
    }

    public function update(Complaint $complaint, ComplaintRequest $request)
    {
        $complaint->update($request->all());

        return new ComplaintListResource($complaint);
    }

    public function show(Complaint $complaint)
    {
        return new ComplaintResource($complaint);
    }

    public function destroy(Complaint $complaint)
    {
        $complaint->delete();
    }

    /**
     * Используется для получения доступных программ в ClientComplaint/Dialog
     */
    public function availablePrograms(Request $request)
    {
        $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'teacher_id' => ['required', 'exists:teachers,id'],
        ]);

        return ClientLesson::query()
            ->join('lessons as l', 'l.id', '=', 'client_lessons.lesson_id')
            ->join('contract_version_programs as cvp', 'cvp.id', '=', 'client_lessons.contract_version_program_id')
            ->join('contract_versions as cv', 'cv.id', '=', 'cvp.contract_version_id')
            ->join('contracts as c', 'c.id', '=', 'cv.contract_id')
            ->where('c.client_id', $request->client_id)
            ->where('l.teacher_id', $request->teacher_id)
            ->selectRaw('DISTINCT(cvp.program) as `program`')
            ->pluck('program');
    }
}
