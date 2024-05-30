<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientReview;
use Illuminate\Http\Request;

class ClientReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = ClientReview::query()
            ->with(['client', 'teacher'])
            ->latest();
        return $this->handleIndexRequest($request, $query);
    }
}
