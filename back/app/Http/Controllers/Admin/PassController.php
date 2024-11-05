<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PassResource;
use App\Http\Resources\PersonResource;
use App\Models\Pass;
use Illuminate\Http\Request;

class PassController extends Controller
{
    protected $filters = [
        'equals' => ['request_id', 'type'],
        'status' => ['status']
    ];

    public function index(Request $request)
    {
        $query = Pass::orderBy('date', 'desc')->latest();
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, PassResource::class);
    }

    public function permanent(Request $request)
    {
        $request->validate(['entity' => ['required', 'string']]);

        $query = $request->entity::canLogin();

        return $this->handleIndexRequest($request, $query, PersonResource::class);
    }

    public function store(Request $request)
    {
        $pass = auth()->user()->entity->passes()->create(
            $request->all()
        );
        return new PassResource($pass);
    }

    public function update(Pass $pass, Request $request)
    {
        $pass->update($request->all());
        return new PassResource($pass);
    }

    public function destroy(Pass $pass)
    {
        $pass->delete();
    }

    protected function filterStatus($query, $status)
    {
        switch ($status) {
            case 'active':
                $query->whereDoesntHave('passLog')->where('date', '>=', now()->format('Y-m-d'));
                break;

            case 'used':
                $query->whereHas('passLog');
                break;

            case 'expired':
                $query->whereDoesntHave('passLog')->where('date', '<', now()->format('Y-m-d'));
        }
    }
}
