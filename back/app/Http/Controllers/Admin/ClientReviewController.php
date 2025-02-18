<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientReviewListResource;
use App\Http\Resources\ClientReviewResource;
use App\Models\ClientReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientReviewController extends Controller
{
    protected $filters = [
        'equals' => [
            'client_id', 'teacher_id', 'rating', 'program',
        ],
        'examScores' => ['exam_scores'],
        'webReviewExists' => ['web_review_exists'],
        'requirement' => ['requirement'],
    ];

    public function index(Request $request)
    {
        // для быстродействия, если выбран конкретный requirement, то без union
        if ($request->has('requirement')) {
            $query = $request->input('requirement')
                ? ClientReview::selectForUnion()
                : ClientReview::requirements();
        } else {
            $query = DB::table('cr')->withExpression('cr',
                ClientReview::requirements()->union(ClientReview::selectForUnion())
            );
        }

        $query->latest();
        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, ClientReviewListResource::class);
    }

    public function update(ClientReview $clientReview, Request $request)
    {
        $clientReview->update($request->all());

        return $clientReview;
    }

    public function show(ClientReview $clientReview)
    {
        return new ClientReviewResource($clientReview);
    }

    protected function filterRequirement(&$query, $value)
    {
        $value ? $query->whereNotNull('id') : $query->whereNull('id');
    }

    protected function filterWebReviewExists(&$query, $value)
    {
        $condition = $value ? 'NOT EXISTS' : 'EXISTS';

        $query->whereRaw("$condition (
            select 1 from `web_reviews` wr
            where wr.client_id = cr.client_id
        )");
    }

    protected function filterExamScores(&$query, $status)
    {
        switch ($status) {
            // нет баллов = по текущему client_ID вообще нет баллов
            case 'notExists':
                $query->whereRaw('NOT EXISTS (
                    select 1 from exam_scores es
                    where es.client_id = cr.client_id
                )');
                break;

            default:
                $condition = $status === 'existsAvailable' ? 'EXISTS' : 'NOT EXISTS';
                $query->whereRaw('EXISTS (
                    select 1 from exam_scores es
                    where es.client_id = cr.client_id
                )')->whereRaw("$condition (
                    select 1 from exam_scores es
                    where es.client_id = cr.client_id
                    and not exists (
                        select 1 from exam_score_web_review es_wr
                        where es_wr.exam_score_id = es.id
                    )
                )");
                break;
        }
    }
}
