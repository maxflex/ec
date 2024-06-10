<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientReview;
use Illuminate\Http\Request;

class ClientReviewController extends Controller
{
    protected $filters = [
        'equals' => ['client_id']
    ];

    public function index(Request $request)
    {
        $query = ClientReview::query()
            ->with(['client', 'teacher'])
            ->latest();
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query);
    }
}
