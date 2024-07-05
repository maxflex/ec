<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientReview;
use App\Models\FakeReview;
use Illuminate\Http\Request;

class ClientReviewController extends Controller
{
    protected $filters = [
        'equals' => ['client_id', 'teacher_id', 'rating', 'program']
    ];

    public function index(Request $request)
    {
        $query = ClientReview::query()
            ->latest()
            ->prepareForUnion()
            ->with(['teacher', 'client']);

        $fakeQuery = FakeReview::query();

        $this->filter($request, $query);
        $this->filter($request, $fakeQuery);

        $query->union($fakeQuery);

        return $this->handleIndexRequest($request, $query, ReportListResource::class);
    }

    public function update(ClientReview $clientReview, Request $request)
    {
        $clientReview->update($request->all());
        return $clientReview;
    }
}
