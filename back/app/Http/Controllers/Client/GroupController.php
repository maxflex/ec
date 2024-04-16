<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        $query = Group::query()
            ->whereHas(
                'contracts',
                fn ($q) => $q->where('client_id', auth()->id())
            )->with('teacher')
            ->orderBy('id', 'desc');

        return $this->handleIndexRequest($request, $query);
    }
}
