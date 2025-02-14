<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientReviewListResource;
use App\Http\Resources\ClientReviewResource;
use App\Models\ClientReview;
use Illuminate\Http\Request;

class ClientReviewController extends Controller
{
    protected $filters = [
        'equals' => ['client_id', 'teacher_id', 'rating', 'program'],
        'type' => ['type'],
    ];

    public function index(Request $request)
    {
        $query = ClientReview::query()
            ->selectForUnion()
            ->with(['teacher', 'client']);

        $fakeQuery = ClientReview::fakeQuery();

        $this->filter($request, $query);
        $this->filter($request, $fakeQuery);

        $query->union($fakeQuery)->latest();

        return $this->handleIndexRequest($request, $query, ClientReviewListResource::class);
    }

    public function update(ClientReview $clientReview, Request $request)
    {
        $clientReview->update($request->all());

        return $clientReview;
    }

    public function show(ClientReview $clientReview)
    {
        return new ClientReviewResource($clientReview);
    }

    protected function filterType(&$query, $type)
    {
        $type ? $query->whereNotNull('id') : $query->whereNull('id');
    }
}
