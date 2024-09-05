<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Contract, Teacher};
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'year' => ['sometimes', 'required', 'numeric', 'min:2015'],
            'contract_id' => ['sometimes', 'required', 'numeric', 'exists:contracts,id'],
            'teacher_id' => ['sometimes', 'required', 'numeric', 'exists:teachers,id']
        ]);

        $entity = match (true) {
            $request->has('contract_id') => Contract::find($request->contract_id),
            $request->has('teacher_id') => Teacher::find($request->teacher_id)
        };

        return $entity->getBalance($request->year);
    }
}
