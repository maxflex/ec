<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\LessonConductResource;
use App\Http\Resources\LessonListResource;
use App\Http\Resources\LessonResource;
use App\Models\Client;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LessonController extends Controller
{
    protected $filters = [
        'equals' => ['group_id', 'teacher_id', 'date'],
        'group' => ['year'],
    ];

    public function index(Request $request)
    {
        if ($request->has('client_id')) {
            $client = Client::find($request->client_id);
            $lessons = $client->getLessons($request->year);
        } else {
            $query = Lesson::with(['teacher', 'group']);
            $this->filter($request, $query);
            $lessons = $query->get();
        }

        return Lesson::withSequenceNumber($lessons);
    }

    public function show(Lesson $lesson, Request $request)
    {
        if ($request->has('conduct')) {
            return new LessonConductResource($lesson);
        }

        return new LessonResource($lesson);
    }

    public function store(Request $request)
    {
        $lesson = auth()->user()->lessons()->create($request->all());

        return new LessonListResource($lesson);
    }

    /**
     * Групповое добавление
     * POST
     */
    public function bulkStore(Request $request)
    {
        $request->validate([
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d'],
            'teacher_id' => ['required', 'exists:teachers,id'],
            'group_id' => ['required', 'exists:groups,id'],
            'items' => ['required', 'array'],
        ]);

        $from = Carbon::parse($request->input('start_date'));
        $to = Carbon::parse($request->input('end_date'));
        $lessons = [];

        while ($from->lessThanOrEqualTo($to)) {
            $dayOfWeek = ($from->dayOfWeek + 6) % 7;
            foreach ($request->input('items') as $item) {
                if (intval($item['weekday']) !== $dayOfWeek) {
                    continue;
                }
                $lessons[] = auth()->user()->lessons()->create([
                    'date' => $from->format('Y-m-d'),
                    'time' => $item['time'],
                    'cabinet' => $item['cabinet'] ?: null,
                    'group_id' => $request->input('group_id'),
                    'teacher_id' => $request->input('teacher_id'),
                    'price' => $request->input('price') ?: 0,
                    'quarter' => $request->input('quarter') ?: null,
                ]);
            }
            $from->addDay();
        }

        return LessonListResource::collection($lessons);
    }

    public function bulkUpdate(Request $request)
    {
        $lessons = Lesson::whereIn('id', $request->ids)->get();
        $data = array_filter($request->lesson);
        foreach ($lessons as $lesson) {
            $lesson->update($data);
        }
    }

    public function update(Request $request, Lesson $lesson)
    {
        $lesson->update($request->all());

        return new LessonListResource($lesson);
    }

    public function bulkDestroy(Request $request)
    {
        Lesson::whereIn('id', $request->ids)->get()->each->delete();
    }

    public function destroy(Lesson $lesson)
    {
        $lesson->delete();

        return new LessonListResource($lesson);
    }

    protected function filterGroup($query, $value, $field)
    {
        $query->whereHas('group', fn ($q) => $q->where($field, $value));
    }
}
