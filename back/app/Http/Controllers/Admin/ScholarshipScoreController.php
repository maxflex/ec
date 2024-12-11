<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PersonResource;
use App\Models\Client;
use App\Models\ScholarshipScore;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScholarshipScoreController extends Controller
{
    protected $filters = [
        'equals' => ['month']
    ];

    protected $mapFilters = [
        'month' => 's.month'
    ];

    public function __invoke(Request $request)
    {
        $query = ScholarshipScore::getQuery();

        $this->filter($request, $query);

        if ($request->mode === 'clients') {
            $items = $query->selectRaw("
                s.`year`, s.`month`, s.client_id,
                COUNT(DISTINCT ss.id) as scores_count,
                AVG(ss.score) as avg_score
            ")
                ->leftJoin('scholarship_scores as ss', fn($join) =>
                    $join
                        ->on('ss.year', '=', 's.year')
                        ->on('ss.month', '=', 's.month')
                        ->on('ss.client_id', '=', 's.client_id')
                )
                ->groupBy(DB::raw("s.`year`, s.`month`, s.client_id"))
                ->orderBy('avg_score', 'desc')
                ->orderBy('scores_count', 'desc')
                ->get()
                ->transform(function ($item) {
                    $item->client = new PersonResource(Client::find($item->client_id));
                    $item->avg_score = $item->avg_score ? round($item->avg_score, 2) : null;
                    return $item;
                })
                ->sortByDesc('avg_score')
                ->values();
        } else {
            $items = $query->selectRaw("
                s.`year`, s.`month`, s.teacher_id,
                COUNT(DISTINCT ss.id) as scores_count,
                COUNT(*) as total
            ")
                ->leftJoin('scholarship_scores as ss', fn($join) =>
                    $join
                        ->on('ss.year', '=', 's.year')
                        ->on('ss.month', '=', 's.month')
                        ->on('ss.client_id', '=', 's.client_id')
                        ->on('ss.teacher_id', '=', 's.teacher_id')
                        ->on('ss.program', '=', 's.program')
                )
                ->groupBy(DB::raw("s.`year`, s.`month`, s.teacher_id"))
                ->orderBy('scores_count', 'desc')
                ->orderBy('total', 'desc')
                ->get()
                ->transform(function ($item) {
                    $item->teacher = new PersonResource(Teacher::find($item->teacher_id));
                    return $item;
                })
                ->sortBy([
                    ['scores_count', 'desc'],
                    'teacher.last_name',
                    'teacher.first_name',
                    'teacher.middle_name',
                ])
                ->values();
        }

        return paginate($items, [
            'mode' => $request->mode
        ]);
    }
}
