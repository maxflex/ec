<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Client, Group, Teacher};
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function teacher(Teacher $teacher, Request $request)
    {
        $request->validate(['year' => ['required']]);
        return $teacher->getSchedule($request->year);
    }

    public function client(Client $client, Request $request)
    {
        $request->validate(['year' => ['required']]);
        return $client->getSchedule($request->year);
    }

    public function group(Group $group)
    {
        return $group->getSchedule();
    }
}
