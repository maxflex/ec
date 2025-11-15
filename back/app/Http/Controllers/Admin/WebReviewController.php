<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\WebReviewResource;
use App\Models\WebReview;
use Illuminate\Http\Request;

class WebReviewController extends Controller
{
    protected $filters = [
        'equals' => ['client_id'],
        'program' => ['program'],
    ];

    public function index(Request $request)
    {
        $query = WebReview::query()
            ->with(['client'])
            ->latest();

        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, WebReviewResource::class);
    }

    public function store(Request $request)
    {
        $webReview = WebReview::create($request->all());
        $webReview->savePrograms($request->programs);

        return new WebReviewResource($webReview);
    }

    public function update(WebReview $webReview, Request $request)
    {
        $webReview->update($request->all());
        $webReview->savePrograms($request->programs);

        return new WebReviewResource($webReview);
    }

    public function show(WebReview $webReview)
    {
        return new WebReviewResource($webReview);
    }

    public function destroy(WebReview $webReview)
    {
        $webReview->delete();
    }

    protected function filterProgram($query, $programs)
    {
        $query->where(function ($q) use ($programs) {
            foreach ($programs as $program) {
                $q->orWhereHas('webReviewPrograms', fn ($q) => $q->where('program', $program));
            }
        });
    }
}
