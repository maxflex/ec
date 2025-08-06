<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientLessonResource;
use App\Http\Resources\LessonResource;
use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    protected $filters = [
        'equals' => ['group_id'],
        'group' => ['year'],
    ];

    public function index(Request $request)
    {
        if ($request->has('client_id')) {
            $lessons = auth()->user()->getJournal($request->year);
        } else {
            $query = Lesson::with(['teacher', 'group', 'clientLessons']);
            $this->filter($request, $query);
            $lessons = $query->get();
        }

        return Lesson::withSequenceNumber($lessons);
    }

    // TODO: проанализировать использование, выглядит странно
    public function show(Lesson $lesson)
    {
        $clientLesson = $lesson->clientLessons()
            ->whereIn(
                'contract_version_program_id',
                auth()->user()->getContractVersionProgramIds($lesson->group->year),
            )
            ->first();

        return [
            'lesson' => new LessonResource($lesson),
            'clientLesson' => $clientLesson ? new ClientLessonResource($clientLesson) : null,
        ];
    }

    protected function filterGroup($query, $value, $field)
    {
        $query->whereHas('group', fn ($q) => $q->where($field, $value));
    }
}
