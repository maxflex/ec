<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\InstructionListResource;
use App\Http\Resources\InstructionResource;
use App\Http\Resources\InstructionTeacherResource;
use App\Models\Instruction;
use Illuminate\Http\Request;

class InstructionController extends Controller
{
    protected $filters = [
        'signed' => ['signed']
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        if ($request->has('teacher_id')) {
            $query = Instruction::queryForTeacher($request->teacher_id);
            $resource = InstructionTeacherResource::class;
        } else {
            $query = Instruction::query()
                ->lastVersions()
                ->latest()
                ->withCount('versions', 'signs');
            $resource = InstructionListResource::class;
        }
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, $resource);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $instruction = auth()->user()->entity->instructions()->create($request->all());
        $instruction->loadCount('versions', 'signs');
        return new InstructionListResource($instruction);
    }

    /**
     * Display the specified resource.
     */
    public function show(Instruction $instruction)
    {
        return new InstructionResource($instruction);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Instruction $instruction)
    {
        $instruction->update($request->all());
        return new InstructionResource($instruction);
    }

    public function diff(Instruction $instruction)
    {
        return $instruction->getDiff();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Instruction $instruction)
    {
        $instruction->delete();
    }

    protected function filterSigned(&$query, $value)
    {
        $value
            ? $query->whereNotNull('signed_at')
            : $query->whereNull('signed_at');
    }
}
