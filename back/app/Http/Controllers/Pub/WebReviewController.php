<?php

namespace App\Http\Controllers\Pub;

use App\Http\Controllers\Controller;
use App\Http\Resources\WebReviewPubResource;
use App\Models\WebReview;
use Illuminate\Http\Request;

class WebReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = WebReview::with('client', 'client.photo')->inRandomOrder(
            $request->input('seed')
        );
        // в старой системе отображались только отзывы с фотками
        $query->whereHas('client.photo')->where('rating', 5);
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, WebReviewPubResource::class);
    }

    // Не используется
    public function show(WebReview $webReview)
    {
        return new WebReviewPubResource($webReview);
    }
}
