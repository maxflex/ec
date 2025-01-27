<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContractEditPriceResource;
use App\Models\ContractVersionProgram;
use Illuminate\Http\Request;

class ContractVersionProgramController extends Controller
{
    // нужно для редактирования цены конкретного ClientLesson, в селекторе договора
    public function __invoke(Request $request)
    {
        $program = ContractVersionProgram::find($request->id);
        $contract = $program->contractVersion->contract;

        $data = ContractVersionProgram::query()
            ->where('program', $program->program)
            ->whereHas('contractVersion',
                fn($q) => $q
                    ->where('is_active', true)
                    ->whereHas('contract', fn($q) => $q
                        ->where('year', $contract->year)
                        ->where('client_id', $contract->client_id)
                    )
            )
            ->get();

        return ContractEditPriceResource::collection($data);
    }
}
