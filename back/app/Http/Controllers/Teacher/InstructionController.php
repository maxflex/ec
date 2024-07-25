<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Resources\InstructionResource;
use App\Http\Resources\InstructionTeacherResource;
use App\Models\Instruction;
use Illuminate\Http\Request;

class InstructionController extends Controller
{
    protected $filters = [
        'signed' => ['signed']
    ];

    public function index(Request $request)
    {
        $query = Instruction::queryForTeacher(auth()->id());
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, InstructionTeacherResource::class);
    }

    public function show(Instruction $instruction)
    {
        return new InstructionResource($instruction);
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
        return new InstructionResource($instruction);
    }

    protected function filterSigned(&$query, $value)
    {
        $value
            ? $query->whereNotNull('signed_at')
            : $query->whereNull('signed_at');
    }
}
