<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\GroupActResource;
use App\Models\GroupAct;
use Illuminate\Http\Request;

class GroupActController extends Controller
{
    protected $filters = [
        'equals' => ['group_id'],
    ];

    public function index(Request $request)
    {
        $query = GroupAct::with(['user', 'teacher']);
        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, GroupActResource::class);
    }

    public function store(Request $request)
    {
        $groupAct = auth()->user()->groupActs()->create($request->all());

        return new GroupActResource($groupAct);
    }

    public function show(GroupAct $groupAct)
    {
        return $groupAct;
    }

    public function update(Request $request, GroupAct $groupAct)
    {
        $groupAct->update($request->all());

        return new GroupActResource($groupAct);
    }

    public function destroy(GroupAct $groupAct)
    {
        $groupAct->delete();
    }
}
