<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\InstructionStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\InstructionListResource;
use App\Http\Resources\InstructionResource;
use App\Models\Instruction;
use Illuminate\Http\Request;

class InstructionCheckController extends Controller
{
    public function index(Request $request)
    {
        $query = Instruction::query()
            ->lastVersions(false)
            ->withCount(['versions', 'signs'])
            ->whereIn('status', [
                InstructionStatus::toCheckTeacher,
                InstructionStatus::finalCheckBeforePublished,
                InstructionStatus::published,
            ])
            ->latest();

        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, InstructionListResource::class);
    }

    public function show($id)
    {
        $instruction = Instruction::findOrFail($id);

        return new InstructionResource($instruction);
    }

    public function update($id, Request $request)
    {
        $instruction = Instruction::findOrFail($id);
        $instruction->update($request->all());

        return new InstructionResource($instruction);
    }
}
