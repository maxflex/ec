<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClientReviewRequest;
use App\Http\Resources\ClientReviewListResource;
use App\Http\Resources\ClientReviewResource;
use App\Models\ClientReview;
use Illuminate\Http\Request;

class ClientReviewController extends Controller
{
    protected $filters = [
        'equals' => ['client_id', 'teacher_id', 'program'],
    ];

    public function index(Request $request)
    {
        $query = ClientReview::latest();
        $this->filter($request, $query);

        return $this->handleIndexRequest(
            $request,
            $query,
            ClientReviewListResource::class
        );
    }

    public function store(ClientReviewRequest $request)
    {
        $clientReview = ClientReview::create($request->all());

        return new ClientReviewListResource($clientReview);
    }

    public function update(ClientReview $clientReview, ClientReviewRequest $request)
    {
        $clientReview->update($request->all());

        return new ClientReviewListResource($clientReview);
    }

    public function show(ClientReview $clientReview)
    {
        return new ClientReviewResource($clientReview);
    }

    public function destroy(ClientReview $clientReview)
    {
        $clientReview->delete();
    }
}
