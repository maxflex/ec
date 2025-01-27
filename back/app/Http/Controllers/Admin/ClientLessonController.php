<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientLessonResource;
use App\Models\ClientLesson;
use Illuminate\Http\Request;

class ClientLessonController extends Controller
{
    public function show(ClientLesson $clientLesson)
    {
        return new ClientLessonResource($clientLesson);
    }

    public function update(ClientLesson $clientLesson, Request $request)
    {
        $clientLesson->update($request->all());
        return new ClientLessonResource($clientLesson);
    }

    public function destroy(ClientLesson $clientLesson)
    {
        $clientLesson->delete();
    }
}
