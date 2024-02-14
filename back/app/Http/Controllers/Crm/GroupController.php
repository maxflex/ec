<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Http\Resources\GroupResource;
use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        $query = Group::query()
            ->with('teacher')
            ->orderBy('id', 'desc');
        return $this->handleIndexRequest($request, $query);
    }

    public function show($id)
    {
        $group = Group::find($id);
        return new GroupResource($group);
    }
}
