<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\{LessonResource, LessonListResource};
use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    protected $filters = [
        'equals' => ['group_id']
    ];

    public function index(Request $request)
    {
        $query = Lesson::query()
            ->with('teacher')
            ->orderBy('start_at');
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, LessonListResource::class);
    }

    public function show(Lesson $lesson)
    {
        return new LessonResource($lesson);
    }

    public function update(Request $request, Lesson $lesson)
    {
        $lesson->update($request->all());
        return new LessonListResource($lesson);
    }

    public function store(Request $request)
    {
        $lesson = auth()->user()->entity->lessons()->create($request->all());
        return new LessonListResource($lesson);
    }

    public function destroy(Lesson $lesson)
    {
        $lesson->delete();
        return new LessonListResource($lesson);
    }
}
