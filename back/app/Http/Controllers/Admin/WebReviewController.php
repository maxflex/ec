<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\WebReviewResource;
use App\Models\WebReview;
use Illuminate\Http\Request;

class WebReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = WebReview::query()
            ->with(['client', 'scores'])
            ->latest();
        return $this->handleIndexRequest($request, $query, WebReviewResource::class);
    }

    public function update(WebReview $webReview, Request $request)
    {
        $webReview->update($request->all());
        return new WebReviewResource($webReview);
    }
}
