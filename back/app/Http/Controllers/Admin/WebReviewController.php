<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\WebReviewResource;
use App\Models\WebReview;
use Illuminate\Http\Request;

class WebReviewController extends Controller
{
    protected $filters = [
        'equals' => ['client_id']
    ];

    public function index(Request $request)
    {
        $query = WebReview::query()
            ->with(['client', 'scores'])
            ->latest();
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
