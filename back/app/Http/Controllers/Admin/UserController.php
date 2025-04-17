<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $filters = [
        'equals' => ['is_active'],
        'search' => ['q'],
    ];

    public function index(Request $request)
    {
        $query = User::latest('is_active')
            ->orderBy('id');
        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, UserResource::class);
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
        ]);
        $user = User::create($request->all());
        sync_relation($user, 'phones', $request->all());

        return new UserResource($user);
    }

    public function show(User $user)
    {
        return new UserResource($user);
    }

    public function update(User $user, Request $request)
    {
        $user->update($request->all());
        sync_relation($user, 'phones', $request->all());

        return new UserResource($user);
    }

    public function destroy(User $user)
    {
        $user->phones->each->delete();
        $user->delete();

        return new UserResource($user);
    }

    protected function filterSearch($query, $value)
    {
        $words = explode(' ', $value);
        $query->where(function ($q) use ($words) {
            foreach ($words as $word) {
                $q->where('first_name', 'like', "%{$word}%")
                    ->orWhere('last_name', 'like', "%{$word}%");
            }
        });
    }
}
