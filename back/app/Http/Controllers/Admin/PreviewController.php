<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PreviewRequest;
use App\Http\Resources\AuthResource;
use App\Models\Phone;

class PreviewController extends Controller
{
    public function enter(PreviewRequest $request)
    {
        $phone = Phone::query()
            ->where('entity_id', $request->id)
            ->where('entity_type', $request->entity_type)
            ->first();
        return [
            'user' => new AuthResource($phone),
            'token' => $phone->createSessionToken(),
        ];
    }
}
