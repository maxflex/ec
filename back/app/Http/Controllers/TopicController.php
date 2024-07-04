<?php

namespace App\Http\Controllers;

use App\Http\Resources\TopicListResource;
use App\Models\Lesson;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    protected $filters = [
        'equals' => ['is_topic_verified'],
        'group' => ['year'],
    ];

    public function index(Request $request)
    {
        $request->validate(['year' => ['required']]);
        $query = Lesson::query()
            ->with(['teacher'])
            ->whereNotNull('topic')
            ->orderBy('start_at', 'desc');
        $this->filter($request, $query);
        return $this->handleIndexRequest(
            $request,
            $query,
            TopicListResource::class
        );
    }

    protected function filterGroup(&$query, $value, $field)
    {
        $query->whereHas('group', fn ($q) => $q->where($field, $value));
    }
}
