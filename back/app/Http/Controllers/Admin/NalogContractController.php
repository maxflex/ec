<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Company;
use App\Enums\LessonStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\PersonResource;
use App\Models\ClientGroup;
use App\Models\ClientLesson;
use App\Models\Contract;

class NalogContractController extends Controller
{
    public function __invoke()
    {
        $date1 = '2024-12-31';
        $date2 = '2025-02-28';

        $contracts = Contract::with('client.parent')
            ->where('year', 2024)
            ->where('company', Company::ip)
            ->get();

        $result = collect();

        foreach ($contracts as $contract) {
            $activeVersion = $contract->active_version;
            foreach ($activeVersion->programs as $program) {
                $sum1 = ClientLesson::query()
                    ->where('contract_version_program_id', $program->id)
                    ->join('lessons as l', 'l.id', '=', 'client_lessons.lesson_id')
                    ->where('l.date', '<=', $date1)
                    ->sum('client_lessons.price');

                $sum2 = $sum1 + ClientLesson::query()
                        ->where('contract_version_program_id', $program->id)
                        ->join('lessons as l', 'l.id', '=', 'client_lessons.lesson_id')
                        ->where('l.date', '>', $date1)
                        ->where('l.date', '<=', $date2)
                        ->sum('client_lessons.price');

                $group = ClientGroup::query()
                    ->where('contract_version_program_id', $program->id)
                    ->first()
                    ?->group;

                if ($group) {
                    $plannedLessons = $group
                        ->lessons()
                        ->where('status', LessonStatus::planned)
                        ->where('is_free', false)
                        ->get();

                    foreach ($plannedLessons as $i => $plannedLesson) {
                        $nextPrice = $program->getNextPrice($i + 1);
                        if ($plannedLesson->date <= $date1) {
                            $sum1 += $nextPrice;
                        }
                        if ($plannedLesson->date <= $date2) {
                            $sum2 += $nextPrice;
                        }
                    }
                }

            }

            $result->push([
                'id' => $contract->id,
                'parent' => new PersonResource($contract->client->parent),
                'seq' => $activeVersion->seq,
                'date' => $activeVersion->date,
                'sum1' => $sum1,
                'sum2' => $sum2,
            ]);
        }

        return $result->sortBy([
            'parent.last_name',
            'parent.first_name',
            'parent.middle_name',
        ])->values();
    }
}
