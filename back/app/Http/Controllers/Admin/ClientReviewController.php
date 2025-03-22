<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientReviewListResource;
use App\Http\Resources\ClientReviewResource;
use App\Models\ClientReview;
use App\Utils\ClientReviewMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientReviewController extends Controller
{
    protected $filters = [
        'equals' => ['client_id', 'teacher_id', 'program', 'is_marked'],
        'examScoresExists' => ['exam_scores_exists'],
        'webReviewExists' => ['web_review_exists'],
        'year' => ['year'],
    ];

    public function index(Request $request)
    {
        $requirement = $request->has('requirement') ? intval($request->input('requirement')) : null;

        // для быстродействия, если выбран конкретный requirement, то без union
        $cr = match (true) {
            $requirement === 0 => ClientReview::requirements(),
            $requirement === 1 => ClientReview::selectForUnion(),
            default => ClientReview::requirements()->union(ClientReview::selectForUnion())
        };

        $query = DB::table('cr')->withExpression('cr', $cr);

        $query->latest();
        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, ClientReviewListResource::class);
    }

    public function update(ClientReview $clientReview, Request $request)
    {
        $clientReview->update($request->all());

        return new ClientReviewListResource($clientReview);
    }

    public function show(ClientReview $clientReview)
    {
        return new ClientReviewResource($clientReview);
    }

    public function store(Request $request)
    {
        $clientReview = auth()->user()->clientReviews()->create($request->all());

        return new ClientReviewListResource($clientReview);
    }

    public function destroy(ClientReview $clientReview)
    {
        $clientReview->delete();
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'id' => ['required', 'string'],
        ]);

        $clientReviewMessage = new ClientReviewMessage($request->input('id'));
        $sent = $clientReviewMessage->sendRatingMessage();

        abort_unless($sent, 422);
    }

    protected function filterWebReviewExists(&$query, $value)
    {
        $condition = $value ? 'NOT EXISTS' : 'EXISTS';

        $query->whereRaw("$condition (
            select 1 from `web_reviews` wr
            where wr.client_id = cr.client_id
        )");
    }

    protected function filterExamScoresExists(&$query, $value)
    {
        $condition = $value ? 'NOT EXISTS' : 'EXISTS';

        $query->whereRaw("$condition (
            select 1 from exam_scores es
            where es.client_id = cr.client_id
        )");
    }

    protected function filterYear(&$query, $year)
    {
        $query->whereRaw("EXISTS (
            select 1 from client_lessons cl
            join contract_version_programs cvp
                on cvp.id = cl.contract_version_program_id
                and cvp.program = cr.program
            join contract_versions cv on cv.id = cvp.contract_version_id
            join contracts c
                on c.id = cv.contract_id
                and c.year = $year
                and c.client_id = cr.client_id
            join lessons l
                on l.id = cl.lesson_id
                and l.teacher_id = cr.teacher_id
        )");
    }
}
