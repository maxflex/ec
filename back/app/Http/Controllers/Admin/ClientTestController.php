<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientTestResource;
use App\Models\ClientTest;
use App\Models\Test;
use Illuminate\Http\Request;

class ClientTestController extends Controller
{
    protected $filters = [
        'equals' => ['client_id', 'year', 'program'],
        'status' => ['status']
    ];

    public function index(Request $request)
    {
        $query = ClientTest::query();
        if (!$request->has('client_id')) {
            $query->with('client');
        }
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, ClientTestResource::class);
    }

    public function store(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:tests,id'],
            'client_id' => ['required', 'exists:clients,id'],
            'year' => ['required', 'numeric'],
        ]);

        $tests = Test::whereIn('id', $request->ids)->get();
        $clientTests = [];
        foreach ($tests as $test) {
            $clientTests[] = auth()->user()->entity->clientTests()->create([
                ...$request->all(),
                'program' => $test->program,
                'name' => $test->name,
                'file' => $test->getRawOriginal('file'),
                'minutes' => $test->minutes,
                'questions' => $test->questions,
            ]);
        }

        return ClientTestResource::collection($clientTests);
    }

    public function show(ClientTest $clientTest)
    {
        return new ClientTestResource($clientTest);
    }

    public function destroy(ClientTest $clientTest)
    {
        $clientTest->delete();
    }

    protected function filterStatus(&$query, $status)
    {
        switch ($status) {
            case 'active':
                $query->active();
                break;
            case 'finished':
                $query->finished();
                break;
            case 'new':
                $query->whereNull('started_at');
                break;
        }
    }
}
