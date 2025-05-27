<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientsBrowseResource;
use App\Models\Client;
use App\Models\ClientParent;
use Illuminate\Http\Request;

class ClientsBrowseController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate(['entity' => ['required', 'string']]);
        $entity = $request->entity;

        $query = $entity::canLogin()
            ->with(['phones'])
            ->orderByRaw('last_name, first_name');

        switch ($entity) {
            case Client::class:
                $query->with(['contracts.versions.programs']);
                break;

            case ClientParent::class:
                $query->with(['client.contracts.versions.programs']);
                break;
        }

        return $this->handleIndexRequest($request, $query, ClientsBrowseResource::class);
    }
}
