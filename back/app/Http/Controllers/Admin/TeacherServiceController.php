<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeacherServiceResource;
use App\Models\TeacherService;
use Illuminate\Http\Request;

class TeacherServiceController extends Controller
{
    protected $filters = [
        'equals' => ['teacher_id', 'year']
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TeacherService::latest();
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, TeacherServiceResource::class);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $teacherService = auth()->user()->entity->teacherServices()->create($request->all());
        return new TeacherServiceResource($teacherService);
    }

    /**
     * Display the specified resource.
     */
    public function show(TeacherService $teacherService)
    {
        return new TeacherServiceResource($teacherService);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TeacherService $teacherService)
    {
        $teacherService->update($request->all());
        return new TeacherServiceResource($teacherService);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TeacherService $teacherService)
    {
        $teacherService->delete();
    }
}
