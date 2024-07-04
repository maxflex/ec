<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\{LessonResource, LessonListResource};
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LessonController extends Controller
{
    protected $filters = [
        'equals' => ['group_id', 'teacher_id'],
        'group' => ['year'],
        'client' => ['client_id']
    ];

    public function index(Request $request)
    {
        $query = Lesson::query()
            ->with(['teacher', 'group', 'contractLessons'])
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

    /**
     * Групповое добавление
     * @method POST
     */
    public function batchStore(Request $request)
    {
        $from = Carbon::parse($request->batch['start_date']);
        $to = Carbon::parse($request->batch['end_date']);
        $data = $request->lesson;
        $lessons = [];

        while ($from->lessThanOrEqualTo($to)) {
            $dayOfWeek = ($from->dayOfWeek + 6) % 7;
            $time = $request->batch['weekdays'][$dayOfWeek];
            if ($time) {
                $data['start_at'] = join(' ', [$from->format('Y-m-d'), $time]);
                $lessons[] = auth()->user()->entity->lessons()->create($data);
            }
            $from->addDay();
        }

        return LessonListResource::collection($lessons);
    }

    public function batchUpdate(Request $request)
    {
        $lessons = Lesson::whereIn('id', $request->ids)->get();
        $data = array_filter($request->lesson);
        foreach ($lessons as $lesson) {
            $lesson->update($data);
        }
    }

    public function batchDestroy(Request $request)
    {
        Lesson::whereIn('id', $request->ids)->get()->each->delete();
    }

    public function destroy(Lesson $lesson)
    {
        $lesson->delete();
        return new LessonListResource($lesson);
    }

    protected function filterGroup(&$query, $value, $field)
    {
        $query->whereHas('group', fn ($q) => $q->where($field, $value));
    }

    protected function filterClient(&$query, $clientId)
    {
        $query->whereHas(
            'group',
            fn ($q) => $q->whereHas(
                'contracts',
                fn ($z) => $z
                    ->where('year', request()->year)
                    ->where('client_id', $clientId)
            )
        );
    }
}
