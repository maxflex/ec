<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientsBrowseResource;
use Illuminate\Http\Request;

class ClientsBrowseController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate(['entity' => ['required', 'string']]);

        $query = $request->entity::canLogin();

        return $this->handleIndexRequest($request, $query, ClientsBrowseResource::class);
    }
}
