<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PersonResource;
use App\Models\Client;
use App\Models\ScholarshipScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScholarshipScoreController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = ScholarshipScore::getQuery();
        $query = DB::table(DB::raw("({$query->toSql()}) as s"))
            ->mergeBindings($query)
            ->where('s.month', $request->month);

        if ($request->mode === 'clients') {
            $items = $query->selectRaw("
                s.`year`, s.`month`, s.client_id,
                COUNT(DISTINCT ss.id) as scores_count,
                AVG(ss.score) as avg_score
            ")
                ->leftJoin('scholarship_scores as ss', function ($join) {
                    $join
                        ->on('ss.year', '=', 's.year')
                        ->on('ss.month', '=', 's.month')
                        ->on('ss.client_id', '=', 's.client_id');
                })
                ->groupBy(DB::raw("`year`, `month`, client_id"))
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
                ->leftJoin('scholarship_scores as ss', function ($join) {
                    $join
                        ->on('ss.year', '=', 's.year')
                        ->on('ss.month', '=', 's.month')
                        ->on('ss.client_id', '=', 's.client_id')
                        ->on('ss.grade_id', '=', 's.grade_id')
                        ->on('ss.subject_id', '=', 's.subject_id')
                        ->on('ss.teacher_id', '=', 's.teacher_id');
                })
                ->groupBy(DB::raw("`year`, `month`, teacher_id"))
                ->orderBy('scores_count', 'desc')
                ->orderBy('total', 'desc')
                ->get();
        }

        return paginate($items, [
            'mode' => $request->mode
        ]);
    }
}
