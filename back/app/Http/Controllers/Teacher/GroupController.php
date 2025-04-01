<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Resources\GroupResource;
use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends \App\Http\Controllers\Admin\GroupController
{
    public function index(Request $request)
    {
        // препод может видеть только свои группы
        // (исключение – классрук, он может видеть группы клиента)
        $isHeadTeacherView = $request->has('client_id') && auth()->user()->is_head_teacher;
        if (! $isHeadTeacherView) {
            $request->merge([
                'teacher_id' => auth()->id(),
            ]);
        }

        return parent::index($request);
    }

    public function show(Group $group)
    {
        // препод может видеть только группы, в которых у него были занятия
        // https://doc.ege-centr.ru/tasks/834
        abort_if(
            ! $group->lessons()->where('teacher_id', auth()->id())->exists(),
            403
        );

        return new GroupResource($group);
    }
}
