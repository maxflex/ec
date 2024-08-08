<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\WebReviewResource;
use App\Models\WebReview;
use Illuminate\Http\Request;

class WebReviewController extends Controller
{
    protected $filters = [
        'equals' => ['client_id', 'is_published'],
        'has' => ['has_exam_score'],
    ];

    public function index(Request $request)
    {
        $query = WebReview::query()
            ->with('client')
            ->latest();

        if ($request->has('exam_score_id')) {
            $query->with('examScore',
                fn($q) => $q->where('id', '<>', $request->exam_score_id)
            );
        } else {
            $query->with('examScore');
        }
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, WebReviewResource::class);
    }

    public function store(Request $request)
    {
        $webReview = auth()->user()->entity->webReviews()->create($request->all());
        return new WebReviewResource($webReview);
    }

    public function update(WebReview $webReview, Request $request)
    {
        $webReview->update($request->all());
        return new WebReviewResource($webReview);
    }

    public function destroy(WebReview $webReview)
    {
        $webReview->scores->each->delete();
        $webReview->delete();
    }
}
