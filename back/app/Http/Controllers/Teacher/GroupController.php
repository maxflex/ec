<?php

namespace App\Http\Controllers\Teacher;

use Illuminate\Http\Request;

class GroupController extends \App\Http\Controllers\Admin\GroupController
{
    public function index(Request $request)
    {
        // препод может видеть только свои группы
        // (исключение – классрук, он может видеть группы клиента)
        if (!auth()->user()->entity->is_head_teacher) {
            $request->merge([
                'teacher_id' => auth()->id()
            ]);
        }

        return parent::index($request);
    }
}
