<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SmsResource;
use App\Utils\Sms;
use Illuminate\Http\Request;

class SmsController extends Controller
{
    public function index(Request $request)
    {
        $data = Sms::history($request);

        return [
            'data' => SmsResource::collection($data),
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'total' => count($data),
            ],
        ];
    }
}
