<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\LessonListResource;
use App\Models\{Client, Group, Teacher};
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function teacher(Teacher $teacher, Request $request)
    {
        $request->validate(['year' => ['required']]);
        return LessonListResource::collection(
            $teacher->getSchedule($request->year)
        );
    }

    public function client(Client $client, Request $request)
    {
        $request->validate(['year' => ['required']]);
        $schedule = $client->getSchedule($request->year);
        return LessonListResource::collection($schedule);
    }

    public function group(Group $group)
    {
        return LessonListResource::collection(
            $group->getSchedule()
        );
    }
}
