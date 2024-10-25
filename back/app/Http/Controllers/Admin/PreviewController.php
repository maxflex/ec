<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PreviewRequest;
use App\Http\Resources\AuthResource;
use App\Models\Phone;

class PreviewController extends Controller
{
    public function __invoke(PreviewRequest $request)
    {
        $phone = Phone::where($request->all())->first();
        return [
            'user' => new AuthResource($phone),
            'token' => $phone->createSessionToken(),
        ];
    }
}
