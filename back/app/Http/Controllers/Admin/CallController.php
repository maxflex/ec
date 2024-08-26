<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CallListResource;
use App\Models\Call;
use Illuminate\Http\Request;

class CallController extends Controller
{
    public function index(Request $request)
    {
//        return Call::getActive();
        $query = Call::query();
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, CallListResource::class);
    }

    public function recording($action, Call $call)
    {
        return $call->getRecording($action);
    }
}
