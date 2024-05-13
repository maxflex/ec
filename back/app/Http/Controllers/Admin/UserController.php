<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();
        return $this->handleIndexRequest($request, $query);
    }

    public function show(User $user)
    {
        return $user;
    }
}
