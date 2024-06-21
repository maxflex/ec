<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientReview;
use Illuminate\Http\Request;

class ClientReviewController extends Controller
{
    protected $filters = [
        'equals' => ['client_id', 'teacher_id', 'rating', 'program', 'year']
    ];

    public function index(Request $request)
    {
        $query = ClientReview::latest();
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query);
    }

    public function update(ClientReview $clientReview, Request $request)
    {
        $clientReview->update($request->all());
        return $clientReview;
    }
}
