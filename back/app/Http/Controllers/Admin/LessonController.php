<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\{LessonConductResource, LessonListResource, LessonResource};
use App\Models\Client;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LessonController extends Controller
{
    protected $filters = [
        'equals' => ['group_id', 'teacher_id'],
        'group' => ['year'],
    ];

    public function index(Request $request)
    {
        if ($request->has('client_id')) {
            $client = Client::find($request->client_id);
            return LessonListResource::collection($client->getSchedule($request->year));
        }

        $query = Lesson::query()
            ->with(['teacher', 'group', 'clientLessons'])
            ->orderByRaw('date, time');
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, LessonListResource::class);
    }

    public function show(Lesson $lesson, Request $request)
    {
        if ($request->has('conduct')) {
            return new LessonConductResource($lesson);
        }
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

    /**
     * Групповое добавление
     * POST
     */
    public function bulkStore(Request $request)
    {
        $from = Carbon::parse($request->bulk['start_date']);
        $to = Carbon::parse($request->bulk['end_date']);
        $data = $request->lesson;
        $lessons = [];

        while ($from->lessThanOrEqualTo($to)) {
            $dayOfWeek = ($from->dayOfWeek + 6) % 7;
            $time = $request->bulk['weekdays'][$dayOfWeek];
            if ($time) {
                $cabinet = $request->bulk['cabinets'][$dayOfWeek];
                $data['date'] = $from->format('Y-m-d');
                $data['time'] = $time;
                $data['cabinet'] = $cabinet ?: null;
                $lessons[] = auth()->user()->entity->lessons()->create($data);
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

    public function bulkDestroy(Request $request)
    {
        Lesson::whereIn('id', $request->ids)->get()->each->delete();
    }

    public function destroy(Lesson $lesson)
    {
        $lesson->delete();
        return new LessonListResource($lesson);
    }

    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => ['file', 'max:10240'], // 10 MB
        ]);
        $file = $request->file('file');
        $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('homework', $fileName);
        return cdn('homework', $fileName);
    }

    protected function filterGroup($query, $value, $field)
    {
        $query->whereHas('group', fn ($q) => $q->where($field, $value));
    }
}
