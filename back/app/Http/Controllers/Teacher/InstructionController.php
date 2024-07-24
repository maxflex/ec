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
        $query = Instruction::query()
            ->withLastVersionsCte()
            ->leftJoin(
                'last_versions',
                fn ($join) => $join
                    ->on('last_versions.max_id', '=', 'instructions.id')
                    ->on('last_versions.entry_id', '=', 'instructions.entry_id')
            )
            ->leftJoin(
                'instruction_signs',
                fn ($join) => $join
                    ->on('instruction_signs.instruction_id', '=', 'instructions.id')
                    ->where('instruction_signs.teacher_id', auth()->id())
            )
            ->whereRaw(<<<SQL
                (instruction_signs.id IS NOT NULL OR last_versions.max_id IS NOT NULL)
            SQL)
            ->selectRaw('instructions.*, signed_at')
            ->orderByRaw(<<<SQL
                if(signed_at is null, 0, 1) asc,
                signed_at desc,
                instructions.created_at desc
            SQL);
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, InstructionTeacherResource::class);
    }

    public function show(Instruction $instruction)
    {
        return new InstructionResource($instruction);
    }

    protected function filterSigned(&$query, $value)
    {
        $value
            ? $query->whereNotNull('signed_at')
            : $query->whereNull('signed_at');
    }
}
