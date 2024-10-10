<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Resources\GroupListResource;
use App\Http\Resources\GroupResource;
use App\Http\Resources\GroupVisitResource;
use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    protected $filters = [
        'equals' => ['program', 'year'],
    ];

    public function index(Request $request)
    {
        $query = Group::query()
            ->whereTeacher(auth()->id())
            ->withCount('lessons', 'clientGroups')
            ->latest('id');

        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, GroupListResource::class);
    }

    public function show($id)
    {
        $group = Group::find($id);
        return new GroupResource($group);
    }


    public function visits(Group $group)
    {
        return GroupVisitResource::collection(
            $group->lessons()->orderByRaw('date, time')->get()
        );
    }
}
