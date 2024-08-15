<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Resources\{InstructionTeacherListResource, InstructionTeacherResource};
use App\Models\Instruction;
use Illuminate\Http\Request;

class InstructionController extends Controller
{
    public function index(Request $request)
    {
        $request->merge(['teacher_id' => auth()->id()]);
        $query = Instruction::query()
            ->lastVersions()
            ->latest();
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, InstructionTeacherListResource::class);
    }

    public function show(Instruction $instruction)
    {
        if ($instruction->isArchive(auth()->id())) {
            return response(status: 404);
        }
        return new InstructionTeacherResource($instruction);
    }


    public function diff(Instruction $instruction)
    {
        return $instruction->getDiff(auth()->id());
    }

    public function sign(Instruction $instruction)
    {
        $instruction->signs()->create([
            'teacher_id' => auth()->id()
        ]);
        return new InstructionTeacherResource($instruction);
    }
}
