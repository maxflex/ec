<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebReview;
use Illuminate\Http\Request;

class WebReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = WebReview::query()
            ->with(['client'])
            ->latest();
        return $this->handleIndexRequest($request, $query);
    }
}
