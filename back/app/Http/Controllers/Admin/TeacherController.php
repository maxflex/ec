<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeacherListResource;
use App\Http\Resources\TeacherResource;
use App\Models\Teacher;
use App\Utils\TeacherStats;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    protected $filters = [
        'equals' => ['status'],
        'findInSet' => ['subjects'],
        'search' => ['q'],
    ];

    public function index(Request $request)
    {
        $query = Teacher::query()
            ->orderByRaw("
                if(`status` = 'active', 1, 0) desc,
                concat(last_name, first_name, middle_name) asc
            ");
        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, TeacherListResource::class);
    }

    public function show(Teacher $teacher)
    {
        return new TeacherResource($teacher);
    }

    public function update(Teacher $teacher, Request $request)
    {
        $teacher->update($request->all());
        sync_relation($teacher, 'phones', $request->all());

        return new TeacherResource($teacher);
    }

    public function store(Request $request)
    {
        $teacher = auth()->user()->teachers()->create(
            $request->all()
        );
        sync_relation($teacher, 'phones', $request->all());

        return new TeacherResource($teacher);
    }

    public function destroy(Teacher $teacher)
    {
        $teacher->phones->each->delete();
        $teacher->delete();
    }

    public function stats(Teacher $teacher)
    {
        return [
            'teacher' => $teacher->stats,
            'avg' => TeacherStats::loadAvg(),
        ];
    }

    protected function filterSearch(&$query, $value)
    {
        $words = explode(' ', $value);
        $query->where(function ($q) use ($words) {
            foreach ($words as $word) {
                $q->where('first_name', 'like', "%{$word}%")
                    ->orWhere('last_name', 'like', "%{$word}%")
                    ->orWhere('middle_name', 'like', "%{$word}%");
            }
        });
    }
}
