<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserListResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();
        return $this->handleIndexRequest($request, $query, UserListResource::class);
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
        ]);
        $user = User::create($request->all());
        $user->syncRelation($request->all(), 'phones');
        return new UserListResource($user);
    }

    public function show(User $user)
    {
        return new UserListResource($user);
    }

    public function update(User $user, Request $request)
    {
        $user->update($request->all());
        $user->syncRelation($request->all(), 'phones');
        return new UserListResource($user);
    }

    public function destroy(User $user)
    {
        $user->phones->each->delete();
        $user->delete();
        return new UserListResource($user);
    }
}
