<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeacherListResource;
use App\Http\Resources\TeacherResource;
use App\Models\Teacher;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    protected $filters = [
        'equals' => ['status'],
        'findInSet' => ['subjects'],
        'search' => ['q']
    ];

    public function index(Request $request)
    {
        $query = Teacher::query()
            ->orderByRaw(<<<SQL
                if(`status` = 'active', 1, 0) desc,
                concat(last_name, first_name, middle_name) asc
            SQL);
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
        $teacher->syncRelation($request->all(), 'phones');
        return new TeacherResource($teacher);
    }

    public function destroy(Teacher $teacher)
    {
        $teacher->delete();
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
