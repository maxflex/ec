<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Lesson;
use App\Models\Teacher;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'year' => ['sometimes', 'required', 'numeric', 'min:2015'],
            'contract_id' => ['sometimes', 'required', 'numeric', 'exists:contracts,id'],
            'teacher_id' => ['sometimes', 'required', 'numeric', 'exists:teachers,id'],
        ]);

        if ($request->has('available_years')) {
            return $this->availableYears();
        }

        $entity = match (true) {
            $request->has('contract_id') => Contract::find($request->contract_id),
            $request->has('teacher_id') => Teacher::find($request->teacher_id)
        };

        return paginate($entity->getBalance($request->year));
    }

    protected function availableYears()
    {
        return Lesson::where('teacher_id', request()->teacher_id)
            ->join('groups', 'groups.id', '=', 'lessons.group_id')
            ->conducted()
            ->distinct()
            ->pluck('groups.year')
            ->unique()
            ->sortDesc()
            ->values()
            ->all();
    }
}
