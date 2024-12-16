<?php

namespace App\Http\Controllers\Pub;

use App\Http\Controllers\Controller;
use App\Models\WebReview;
use Illuminate\Http\Request;

class WebReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = WebReview::query();
        $this->filter($request, $query);
    }

    public function show(WebReview $webReview)
    {
    }
}
