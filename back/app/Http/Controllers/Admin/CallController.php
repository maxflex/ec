<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CallListResource;
use App\Models\Call;
use Illuminate\Http\Request;

class CallController extends Controller
{
    protected $filters = [
        'equals' => ['number'],
    ];

    public function index(Request $request)
    {
        $query = Call::query()
            ->latest();
        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, CallListResource::class);
    }

    public function active()
    {
        return Call::getActive();
    }

    public function recording($action, Call $call)
    {
        return $call->getRecording($action);
    }

    public function destroy(Call $call)
    {
        if ($call->is_missed && ! $call->is_missed_callback) {
            $call->delete();
        }
    }
}
