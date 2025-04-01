<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\Request;

/**
 * Меню "Клиенты" у классрука
 */
class ClientController extends \App\Http\Controllers\Admin\ClientController
{
    public function index(Request $request)
    {
        $request->merge([
            'head_teacher_id' => auth()->id(),
        ]);

        return parent::index($request);
    }

    public function show(Client $client)
    {
        abort_if($client->head_teacher_id !== auth()->id(), 404);

        return new ClientResource($client);
    }
}
