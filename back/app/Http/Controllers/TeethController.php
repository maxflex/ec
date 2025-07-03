<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Group;
use App\Models\Teacher;
use Illuminate\Http\Request;

class TeethController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'group_id' => ['sometimes', 'numeric', 'exists:groups,id'],
            'client_id' => ['sometimes', 'numeric', 'exists:clients,id'],
            'teacher_id' => ['sometimes', 'numeric', 'exists:teachers,id'],
            'year' => ['required_without:group_id', 'required', 'numeric', 'min:2015'],
        ]);

        $entity = match (true) {
            $request->has('teacher_id') => Teacher::find($request->teacher_id),
            $request->has('client_id') => Client::find($request->client_id),
            $request->has('group_id') => Group::find($request->group_id)
        };

        $year = $request->has('year') ? intval($request->year) : null;

        return $entity->getTeeth($year);
    }
}
