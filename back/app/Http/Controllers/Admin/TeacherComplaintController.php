<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeacherComplaintResource;
use App\Models\TeacherComplaint;
use Illuminate\Http\Request;

class TeacherComplaintController extends Controller
{
    protected $filters = [
        'equals' => ['status', 'recipient'],
    ];

    public function index(Request $request)
    {
        $query = TeacherComplaint::with('teacher')->withCount('comments');

        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, TeacherComplaintResource::class);
    }

    public function show(TeacherComplaint $teacherComplaint)
    {
        return new TeacherComplaintResource($teacherComplaint);
    }

    public function update(TeacherComplaint $teacherComplaint, Request $request)
    {
        $teacherComplaint->update($request->all());

        return new TeacherComplaintResource($teacherComplaint);
    }

    public function destroy(TeacherComplaint $teacherComplaint)
    {
        $teacherComplaint->delete();
    }
}
