<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientLesson;

class ClientLessonController extends Controller
{
    public function destroy(ClientLesson $clientLesson)
    {
        $clientLesson->delete();
    }
}
