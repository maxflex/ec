<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Teacher;
use Illuminate\Http\Request;

/**
 * Получить все возможные годы для срезки
 */
class YearController extends Controller
{
    public function index(Request $request)
    {
        $years = collect();
        if ($request->has('client_id')) {
            $client = Client::findOrFail($request->client_id);
            foreach ($client->contracts as $contract) {
                foreach ($contract->active_version->programs as $program) {
                    if ($program->group) {
                        $years->push($program->group->year);
                    }
                    foreach ($program->clientLessons as $clientLesson) {
                        $years->push($clientLesson->lesson->group->year);
                    }
                }
            }
        }
        if ($request->has('teacher_id')) {
            $teacher = Teacher::findOrFail($request->teacher_id);
            $years = $teacher->lessons()
                ->join('groups', 'groups.id', '=', 'lessons.group_id')
                ->select('year')
                ->groupBy('year')
                ->pluck('year');
        }
        return $years->unique()->sort()->values()->all();
//        return [2015, 2016, 2017, 2018 2019, 2020, 2021];
//        $request->user()->authorizeRoles(['admin']);
    }
}
