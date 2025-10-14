<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ComplaintRequest;
use App\Http\Resources\ComplaintListResource;
use App\Http\Resources\ComplaintResource;
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
}
